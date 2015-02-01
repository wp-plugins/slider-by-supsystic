<?php

/**
 * Class SupsysticSlider_Photos_Controller
 *
 * @package SupsysticSlider\Photos
 */
class SupsysticSlider_Photos_Controller extends SupsysticSlider_Core_BaseController
{

    const STD_VIEW = 'list'; // accepts 'list' or 'block'.

    /**
     * {@inheritdoc}
     */
    protected function getModelAliases()
    {
        return array(
            'resources' => 'SupsysticSlider_Slider_Model_Resources',
            'photos'    => 'SupsysticSlider_Photos_Model_Photos',
            'folders'   => 'SupsysticSlider_Photos_Model_Folders',
            'position'  => 'SupsysticSlider_Photos_Model_Position',
        );
    }

    /**
     * Index Action
     * Shows the list of the all photos
     */
    public function indexAction(Rsc_Http_Request $request)
    {
        if ('gird-gallery-images' === $request->query->get('page')) {
            $redirectUrl = $this->generateUrl('photos');

            return $this->redirect($redirectUrl);
        }

        $folders  = $this->getModel('folders');
        $photos   = $this->getModel('photos');
        $position = $this->getModel('position');

        $images = array_map(
            array($position, 'setPosition'),
            $photos->getAllWithoutFolders()
        );

        return $this->response(
            '@photos/index.twig',
            array(
                'entities' => array(
                    'images'  => $position->sort($images),
                    'folders' => $folders->getAll()
                ),
                'view_type' => $request->query->get('view', self::STD_VIEW),
                'ajax_url'  => admin_url('admin-ajax.php'),
            )
        );
    }

    /**
     * View Action
     * Shows the photos in the selected album
     *
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function viewAction(Rsc_Http_Request $request)
    {
        if (!$request->query->has('folder_id')) {
            $this->redirect(
                $this->getEnvironment()->generateUrl('photos', 'index')
            );
        }

        $stats = $this->getEnvironment()->getModule('stats');
        $stats->save('folders.view');

        $folderId = (int)$request->query->get('folder_id');

        $folders = $this->getModel('folders');

        if (!$folder = $folders->getById($folderId)) {
            $this->redirect(
                $this->getEnvironment()->generateUrl('photos', 'index')
            );
        }

        return $this->response(
            '@photos/view.twig',
            array(
                'folder'    => $folder,
                'ajax_url'  => admin_url('admin-ajax.php'),
                'view_type' => $request->query->get('view', self::STD_VIEW),
            )
        );
    }

    /**
     * Add Action
     * Adds new photos to the database
     *
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function addAction(Rsc_Http_Request $request)
    {
        $env = $this->getEnvironment();
        $resources = $this->getModel('resources');


        $photos = new SupsysticSlider_Photos_Model_Photos();
        if ($env->getConfig()->isEnvironment(
            Rsc_Environment::ENV_DEVELOPMENT
        )
        ) {
            $photos->setDebugEnabled(true);
        }

        $attachment = get_post($request->post->get('attachment_id'));
        $viewType = $request->post->get('view_type');
        $sliderId = $request->post->get('id');

        $stats = $this->getEnvironment()->getModule('stats');
        $stats->save('photos.add');

        if (!$photos->add($attachment->ID, $request->post->get('folder_id', 0))) {
            $response = array(
                'error'   => true,
                'photo'   => null,
                'message' => sprintf(
                    $env->translate('Unable to save chosen photo %s: %s'),
                    $attachment->post_title,
                    $photos->getLastError()
                ),
            );
        } else {
            $photo = $env->getTwig()->render(
                sprintf('@ui/%s/image.twig', $viewType ? $viewType : 'block'),
                array('image' => $photos->getByAttachmentId($attachment->ID))
            );

            if($sliderId && $imageId = $photos->getByAttachmentId($attachment->ID)->id) {
                $resources->add($sliderId, 'image', $imageId);
                $attachmentImage = wp_get_attachment_image_src($attachment->ID, array('60', '60'));
                $imageUrl = $attachmentImage[0];
                $message = '<img src="' . $imageUrl . '"/>Image was successfully imported to the Slider';
            } else {
                $message = 'Error importing image %s to Slider';
            }

            $response = array(
                'error'   => false,
                'photo'   => $photo,
                'message' => sprintf(
                    $env->translate($message),
                    $attachment->post_title
                ),
            );
        }

        return $this->response(Rsc_Http_Response::AJAX, $response);
    }

    /**
     * Add Folder Action
     * Adds the new folder
     *
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function addFolderAction(Rsc_Http_Request $request)
    {
        $env     = $this->getEnvironment();
        $folders = new SupsysticSlider_Photos_Model_Folders();

        $stats = $this->getEnvironment()->getModule('stats');
        $stats->save('folders.add');

        if ($env->getConfig()->isEnvironment(
            Rsc_Environment::ENV_DEVELOPMENT
        )
        ) {
            $folders->setDebugEnabled(true);
        }

        $folderName = $request->post->get('folder_name');
        $viewType = $request->post->get('view_type');

        if (!$folders->add(
            ($folderName) ? $folderName : $env->translate('New Folder')
        )
        ) {
            $response = array(
                'error'  => true,
                'folder' => null,
            );
        } else {
            $folder = $env->getTwig()->render(
                sprintf('@ui/%s/folder.twig', $viewType ? $viewType : 'block'),
                array('folder' => $folders->getById($folders->getInsertId()))
            );

            $response = array(
                'error'  => false,
                'folder' => $folder,
                'id'     => $folders->getInsertId(),
            );
        }

        return $this->response('ajax', $response);
    }

    /**
     * Delete Action
     * Deletes the specified folders and photos
     *
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function deleteAction(Rsc_Http_Request $request)
    {
        $env     = $this->getEnvironment();
        $data    = $request->post->get('data');
        $debug   = $env->getConfig()->isEnvironment(
            Rsc_Environment::ENV_DEVELOPMENT
        );
        $photos  = $this->getModel('photos');
        $folders = $this->getModel('folders');

        $stats = $this->getEnvironment()->getModule('stats');

        if (!$data) {
            return $this->response(
                'ajax',
                array(
                    'error' => true,
                )
            );
        }

        foreach ($data as $type => $identifies) {
            foreach ($identifies as $id) {
                if ($type === 'photo') {
                    $stats->save('photos.delete');
                    $photos->deleteById((int)$id);
                } else {
                    $stats->save('folders.delete');
                    $folders->deleteById((int)$id);
                }
            }
        }

        return $this->response(
            'ajax',
            array(
                'error' => false,
            )
        );
    }

    public function checkPhotoUsageAction(Rsc_Http_Request $request)
    {
        $photoId = $request->post->get('photo_id');

        $photos = $this->getModel('photos');
        $photo  = $photos->getById($photoId);

        $resources = $this->getModel('resources');

        if ($photo->folder_id > 0) {
            $galleries = $resources->getGalleriesWithFolder($photo->folder_id);
        } else {
            $galleries = $resources->getGalleriesWithPhoto($photo->id);
        }

        return $this->response(Rsc_Http_Response::AJAX, array(
            'count' => count($galleries),
        ));
    }

    /**
     * Move Action
     * Moves photos to the folders
     *
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function moveAction(Rsc_Http_Request $request)
    {
        $photos = new SupsysticSlider_Photos_Model_Photos();
        $error  = true;

        if ($this->getEnvironment()->getConfig()->isEnvironment(
            Rsc_Environment::ENV_DEVELOPMENT
        )
        ) {
            $photos->setDebugEnabled(true);
        }

        $photoId  = $request->post->get('photo_id');
        $folderId = $request->post->get('folder_id');

        if ($photos->toFolder($photoId, $folderId)) {
            $error = false;
        }

        return $this->response(
            'ajax',
            array(
                'error' => $error,
            )
        );
    }

    /**
     * Render Action
     * Renders the photos from the folder
     *
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function renderAction(Rsc_Http_Request $request)
    {
        $photos = $request->post->get('photos');

        if (!is_array($photos)) {
            return $this->response(
                'ajax',
                array(
                    'error'  => true,
                    'photos' => null,
                )
            );
        }

        $renders = array();

        foreach ($photos as $photo) {
            $renders[] = $this->getEnvironment()->getTwig()->render(
                '@photos/includes/photo.twig', array('photo' => $photo)
            );
        }

        return $this->response(
            'ajax',
            array(
                'error'  => false,
                'photos' => $renders,
            )
        );
    }

    /**
     * Update Title Action
     * Updates the title of the folder
     *
     * @param Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function updateTitleAction(Rsc_Http_Request $request)
    {
        $env      = $this->getEnvironment();
        $folders  = new SupsysticSlider_Photos_Model_Folders();
        $title    = trim($request->post->get('folder_name'));
        $folderId = $request->post->get('folder_id');

        if (empty($title)) {
            return $this->response(
                'ajax',
                array(
                    'error'   => true,
                    'message' => $env->translate('The title can\'t be empty'),
                )
            );
        }

        if ($folders->updateTitle($folderId, $title)) {
            return $this->response(
                'ajax',
                array(
                    'error'   => false,
                    'message' => $env->translate('Title successfully updated'),
                )
            );
        }

        return $this->response(
            'ajax',
            array(
                'error'   => true,
                'message' => $env->translate(
                        'Unable to update the title. Try again later'
                ),
            )
        );
    }

    public function isEmptyAction()
    {
        $debugEnabled = $this->getEnvironment()->isDev();

        $isEmpty = true;
        $photos  = new SupsysticSlider_Photos_Model_Photos($debugEnabled);

        $list = $photos->getAll();

        if (count($list) > 0) {
            $isEmpty = false;
        }

        return $this->response(
            Rsc_Http_Response::AJAX,
            array(
                'isEmpty' => $isEmpty,
            )
        );
    }

    public function updateAttachmentAction(Rsc_Http_Request $request)
    {
//        $alt          = $request->post->get('alt');
        $attachmentId = $request->post->get('attachment_id');
//        $caption      = $request->post->get('caption');
        $description  = $request->post->get('description');

        /** @var SupsysticSlider_Photos_Model_Photos $photos */
        $photos = $this->getModel('photos');

        $photos->updateMetadata($attachmentId, array(
//            'alt'         => $alt,
//            'caption'     => $caption,
            'description' => $description,
        ));

        return $this->response(Rsc_Http_Response::AJAX);
    }

    /**
     * Updates the position of the photo.
     * @param  Rsc_Http_Request $request
     * @return Rsc_Http_Response
     */
    public function updatePositionAction(Rsc_Http_Request $request)
    {
        $response = $this->getErrorResponseData(
            $this->translate('Failed to update position.')
        );

        $data = $request->post->get('data');

        if($this->getModel('position')->update($data)) {
            $response = $this->getSuccessResponseData(
                $this->translate('Position updated successfully!')
            );
        }

        return $this->response(Rsc_Http_Response::AJAX, $response);
    }
}
