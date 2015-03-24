<?php


class SupsysticSlider_Insta_Controller extends SupsysticSlider_Core_BaseController
{

    /**
     * Index page of the Insta module.
     * If user is not authorized yet, then we ask for authorization.
     * Otherwise we show page about current user.
     *
     * @return Rsc_Http_Response
     */

    protected function getModelAliases()
    {
        return array(
            'photos' => 'SupsysticSlider_Photos_Model_Photos',
            'slider' => 'SupsysticSlider_Slider_Model_Sliders',
        );
    }

    public function indexAction(Rsc_Http_Request $request)
    {
        $galleryId = $request->query->get('id');
        $id = get_option('insta_slider_id');
        if($galleryId) {
            if($galleryId != $id) {
                update_option('insta_slider_id', $galleryId);
            }
        } else {
            $galleryId = $id;
        }

        if (!get_option('insta_token')) {
            return $this->redirect(
                $this->generateUrl('insta', 'authorization')
            );
        }

        return $this->response(
            '@insta/index.twig',
            array(
                'images' => get_option('insta_thumbnails'), 'id' => $galleryId
            )
        );
    }

    /**
     * Asks user to authorize with Instagram.
     *
     * @return Rsc_Http_Response
     */
    public function authorizationAction()
    {
        $client = $this->getClient();

        try {
            return $this->response(
                '@insta/authorization.twig',
                array('url' => $client->getAuthorizationUrl())
            );
        } catch (Exception $e) {
            return $this->response(
                'error.twig',
                array('message' => $e->getMessage())
            );
        }
    }

    public function completeAction(Rsc_Http_Request $request)
    {
        $code = $request->query->get('code');

        if (!$code) {
            $message = $this->translate('Authorization code is not specified.');

            return $this->response(
                'error.twig',
                array(
                    'message' => $message,
                )
            );
        }

        try {
            $client = $this->getClient();
            $tmp_arr = $client->requestAccessToken($code);
            $client->setUser($tmp_arr['user']);
            $client->setAccessToken($tmp_arr['access_token']);

            update_option('insta_token', $client->getAccessToken());
            update_option('insta_user', $client->getUser());
            update_option('insta_thumbnails', $client->getUserThumbnails());
        } catch (Exception $e) {
            return $this->response(
                'error.twig',
                array(
                    'message' => $e->getMessage(),
                )
            );
        }

        return $this->redirect($this->generateUrl('insta'));
    }

    public function saveAction(Rsc_Http_Request $request)
    {
        $selectedImages = $request->post->get('urls');
        $photos = $this->getModel('photos');

        $client = $this->getClient();
        $userImages = $client->getUserImages();

        foreach ($userImages as $image) {
            if (in_array($image['standard_resolution']['url'], $selectedImages))
                $attachID[] = $this->media_sideload_image($image['standard_resolution']['url'], 0);
        }

        foreach ($attachID as $id) {
            $photos->add($id);
            $photoId[] = $photos->getByAttachmentId($id)->id;
        }

        return $this->response(Rsc_Http_Response::AJAX, array('msg' => 'Loaded', 'ids' => $photoId));
    }

    public function media_sideload_image($file, $post_id, $desc = null)
    {
        if (!empty($file)) {
            // Set variables for storage, fix file filename for query strings.
            preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches);
            $file_array = array();
            $file_array['name'] = basename($matches[0]);

            // Download file to temp location.
            $file_array['tmp_name'] = download_url($file);

            // If error storing temporarily, return the error.
            if (is_wp_error($file_array['tmp_name'])) {
                return $file_array['tmp_name'];
            }

            // Do the validation and storage stuff.
            $id = media_handle_sideload($file_array, $post_id, $desc);

            // If error storing permanently, unlink.
            if (is_wp_error($id)) {
                @unlink($file_array['tmp_name']);
                return $id;
            }

            $src = wp_get_attachment_url($id);
        }

        // Finally check to make sure the file has been saved, then return the HTML.
        if (!empty($src)) {
            $alt = isset($desc) ? esc_attr($desc) : '';
            $html = "<img src='$src' alt='$alt' />";
            return $id;
        }
    }

    public function logoutAction()
    {
        delete_option('insta_token');
        delete_option('insta_user');
        delete_option('insta_thumbnails');

        return $this->redirect($this->generateUrl('insta'));
    }

    /**
     * @return GridGallery_Insta_Client
     */
    protected function getClient()
    {
        /** @var SupsysticSlider_Insta_Module $insta */
        $insta = $this->getModule($this);

        return $insta->getClient();
    }

} 