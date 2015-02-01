<?php

/**
 * Class SupsysticSlider_Photos_Model_Photo
 *
 * @package SupsysticSlider\Photos\Model
 * @author Artur Kovalevsky
 */
class SupsysticSlider_Photos_Model_Photos extends Rsc_Mvc_Model implements Rsc_Environment_AwareInterface
{

    /**
     * @var bool
     */
    protected $debugEnabled;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var int
     */
    protected $insertId;

    /**
     * @var string
     */
    protected $lastError;

    /**
     * @var Rsc_Environment
     */
    protected $environment;

    /**
     * Constructor
     */
    public function __construct($debugEnabled = false)
    {
        parent::__construct();

        $this->debugEnabled = (bool) $debugEnabled;
        $this->table = $this->db->prefix . 'rs_photos';
    }

    /**
     * @param boolean $debugEnabled
     * @return SupsysticSlider_Photos_Model_Photos
     */
    public function setDebugEnabled($debugEnabled)
    {
        $this->debugEnabled = $debugEnabled;
        return $this;
    }

    /**
     * Returns the identifier of the last inserted photo
     * @return int
     */
    public function getInsertId()
    {
        return $this->insertId;
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Adds the photo to the database.
     * @param int $attachmentId The identifier of the attachment to add
     * @param int $folderId     The identifier of the folder (Default: 0)
     * @return bool
     */
    public function add($attachmentId, $folderId = 0)
    {
        $query = $this->getQueryBuilder()->insertInto($this->table)
            ->fields('attachment_id', 'folder_id')
            ->values((int)$attachmentId, (int)$folderId);

        if (!$this->db->query($query->build())) {
            $this->lastError = $this->db->last_error;
            return false;
        }

        $this->insertId = $this->db->insert_id;
        return true;
    }

    /**
     * Returns the photo by the id
     * @param int $id The identifier of the photo
     * @return object $photo or NULL on failure
     */
    public function getById($id)
    {
        return $this->getBy('id', (int)$id);
    }

    /**
     * Returns the photo by the attachment id
     * @param int $attachmentId The identifier of the attachment
     * @return object $photo or NULL on failure
     */
    public function getByAttachmentId($attachmentId)
    {
        $query = $this->getQueryBuilder()->select('*')
            ->from($this->table)
            ->where('attachment_id', '=', (int) $attachmentId)
            ->orderBy('id')
            ->order('DESC');

        if(null === $photo = $this->db->get_row($query->build(), ARRAY_A)) {
            return null;
        }

        return $this->extend($photo);
    }

    /**
     * Returns the array of the photos linked to the specified folder
     * @param int $folderId The identifier of the folder
     * @return array|null
     */
    public function getPhotosByFolderId($folderId)
    {
        $query = $this->getQueryBuilder()->select('*')
            ->from($this->table)
            ->where('folder_id', '=', (int) $folderId);

        if ($photos = $this->db->get_results($query->build())) {
            foreach ($photos as $index => $photo) {
                $photos[$index] = $this->extend($photo);
            }
        }

        return $photos;
    }

    /**
     * Returns the folder by the photo id
     * @param int $photoId The identifier of the photo
     * @return null|object
     */
    public function getFolderByPhotoId($photoId)
    {
        if(!$photo = $this->getById($photoId)) {
            return null;
        }

        if (!class_exists($classname = 'SupsysticSlider_Photos_Model_Folders', false)) {
            if ($this->debugEnabled) {
                wp_die (sprintf('The required class \'%s\' is does not exists', $classname));
            }

            return null;
        }

        $folders = new SupsysticSlider_Photos_Model_Folders($this->debugEnabled);

        return $folders->getById($photo->folder_id);
    }

    /**
     * Returns the array of the photos
     * @return array|null
     */
    public function getAll()
    {
        $query = $this->getQueryBuilder()->select('*')
            ->from($this->table);

        if ($photos = $this->db->get_results($query->build())) {
            foreach ($photos as $index => $photo) {
                $photos[$index] = $this->extend($photo);
            }
        }

        return $photos;
    }

    /**
     * Returns all photos without folders
     * @return array|null
     */
    public function getAllWithoutFolders()
    {
        $query = $this->getQueryBuilder()->select('*')
            ->from($this->table)
            ->where('folder_id', '=', 0);

        if ($photos = $this->db->get_results($query->build())) {
            foreach ($photos as $index => $photo) {
                $photos[$index] = $this->extend($photo);
            }
        }

        return $photos;
    }

    /**
     * Deletes photo from the plugin by the attachment id
     * @param int $attachmentId The identifier of the attachment
     * @return bool TRUE on success, FALSE otherwise
     */
    public function deleteByAttachmentId($attachmentId)
    {
        $attachmentId = (int)$attachmentId;
        $photo        = $this->getByAttachmentId($attachmentId);

        do_action('gg_delete_photo_attachment_id', $attachmentId);

        return $this->deleteBy('attachment_id', $attachmentId);
    }

    /**
     * Deletes photo from the plugin by the identifier
     * @param int $id The identifier of the photo
     * @return bool TRUE of success, FALSE otherwise
     */
    public function deleteById($id)
    {
        if ($this->deleteBy('id', (int)$id)) {
            if ($this->environment) {
                $this->environment
                    ->getDispatcher()
                    ->dispatch('photos_delete_by_id', array($id));
            }

            return true;
        }

        return false;
    }

    /**
     * Removes photos with specified folder id.
     *
     * @param int $id Folder id.
     * @return bool
     */
    public function deleteByFolderId($id)
    {
        if ($this->deleteBy('folder_id', $id)) {
            if ($this->environment) {
                $this->environment
                    ->getDispatcher()
                    ->dispatch('photos_delete_by_folder_id', array($id));
            }

            return true;
        }

        return false;
    }

    /**
     * Moves selected photo to the selected folder
     * @param int $photoId The identifier of the photo
     * @param int|null $folderId The identifier of the folder
     * @return bool TRUE on success, FALSE otherwise
     */
    public function toFolder($photoId, $folderId = null)
    {
        if (!$this->getById((int) $photoId)) {
            return false;
        }

        $folders = new SupsysticSlider_Photos_Model_Folders($this->debugEnabled);

        $query = $this->getQueryBuilder()->update($this->table)
            ->fields('folder_id')
            ->values(($folderId === null ? $folderId : (int) $folderId))
            ->where('id', '=', (int) $photoId);

        if (!$this->db->query($query->build())) {
            $this->lastError = $this->db->last_error;
            return false;
        }

        return true;
    }

    public function setAlt($attachmentId, $alt)
    {
        $alt = htmlspecialchars($alt, ENT_QUOTES, get_bloginfo('charset'));

        update_post_meta((int)$attachmentId, '_wp_attachment_image_alt', $alt);
    }

    public function setCaption($attachmentId, $caption)
    {
        $caption = htmlspecialchars(
            $caption,
            ENT_QUOTES,
            get_bloginfo('charset')
        );

        wp_update_post(
            array(
                'ID'           => (int)$attachmentId,
                'post_excerpt' => $caption,
            )
        );
    }

    public function setDescription($attachmentId, $description)
    {
        $description = htmlspecialchars(
            $description,
            ENT_QUOTES,
            get_bloginfo('charset')
        );

        wp_update_post(
            array(
                'ID'           => (int)$attachmentId,
                'post_content' => $description,
            )
        );
    }

    public function updateMetadata($attachmentId, array $metadata)
    {
        foreach ($metadata as $key => $value) {
            if (!method_exists($this, $method = sprintf('set%s', ucfirst($key)))) {
                throw new BadMethodCallException(
                    sprintf('The method %s does not exists.', $method)
                );
            }

            call_user_func_array(array($this, $method), array(
                (int)$attachmentId, $value
            ));
        }
    }

    /**
     * Returns the data of the photo by the specified field
     * @param string $field The name of the field
     * @param mixed $identifier The identifier
     * @return object $photo or NULL
     */
    protected function getBy($field, $identifier)
    {
        if ($this->debugEnabled) {

            $metadata = $this->db->get_results(sprintf('SHOW COLUMNS FROM %s', $this->table));
            $fields = array();

            foreach ($metadata as $column) {
                $fields[] = $column->Field;
            }

            if (!in_array($field, $fields)) {
                wp_die (sprintf('The field \'%s\' is does not exists in the table \'%s\'', $field, $this->table));
            }
        }

        $query = $this->getQueryBuilder()->select('*')
            ->from($this->table)
            ->where($field, '=', $identifier);

        if ($photo = $this->db->get_row($query->build())) {
            $photo = $this->extend($photo);
        }

        return $photo;
    }

    /**
     * Deletes row(s) by specified field
     * @param string $field The name of the field
     * @param mixed $identifier The identifier
     * @return bool TRUE on success, FALSE otherwise
     */
    protected function deleteBy($field, $identifier)
    {
        if ($this->debugEnabled) {
            $metadata = $this->db->get_results(sprintf('SHOW COLUMNS FROM %s', $this->table));
            $fields = array();

            foreach ($metadata as $column) {
                $fields[] = $column->Field;
            }

            if (!in_array($field, $fields)) {
                wp_die (sprintf('The field \'%s\' is does not exists in the table \'%s\'', $field, $this->table));
            }
        }

        $query = $this->getQueryBuilder()->deleteFrom($this->table)
            ->where($field, '=', $identifier);

        if (!$this->db->query($query->build())) {
            return false;
        }

        return true;
    }

    public function getUsedTimes($photo)
    {
//        $resources = new SupsysticSlider_Galleries_Model_Resources();
//
//        if ($photo->folder_id > 0) {
//            $galleries = $resources->getGalleriesWithFolder($photo->folder_id);
//        } else {
//            $galleries = $resources->getGalleriesWithPhoto($photo->id);
//        }
//
//        return count($galleries);

        return 0;
    }

    public function getSliderImages($slider)
    {
        $images = array();

        if (!is_object($slider)) {
            throw new InvalidArgumentException(sprintf(
                'Parameter 1 must be a object, %s given.',
                gettype($slider)
            ));
        }

        if (!property_exists($slider, 'resources')) {
            // Nothing to process
            return $slider;
        }


        if (!is_array($slider->resources)) {
            throw new InvalidArgumentException(sprintf(
                'The "resources" property must be an array, %s given.',
                gettype($slider->resources)
            ));
        }

        foreach ($slider->resources as $resource) {
            if ($resource->resource_type === 'image') {
                $image = $this->getById($resource->resource_id);
                $image->rid = $resource->id;

                $images[] = $image;
            }
        }

        if (property_exists($slider, 'images')) {
            if (is_array($slider->images)) {
                $slider->images = array_merge($slider->images, $images);
            } else {
                $slider->images = $images;
            }
        } else {
            $slider->images = $images;
        }

//        if ($this->environment) {
//            /** @var SupsysticSlider_Photos_Module $photos */
//            $photos = $this->environment->getModule('photos');
//
//            $slider->images = $photos->sortImages($slider->images);
//        }

        return $slider;
    }

    /**
     * Extends the default database result for photo
     * @param object|array $photo The default database result for photo
     * @return object
     */
    public function extend($photo)
    {
        if (!is_object($photo) && !is_array($photo)) {
            if ($this->debugEnabled) {
                wp_die ('Invalid $photo parameter specified');
            }
        }

        $photo = (object) $photo;

        $usedTimes = $this->getUsedTimes($photo);

        $photo->attachment = wp_prepare_attachment_for_js($photo->attachment_id);
        $photo->is_used    = (($usedTimes > 0) ? true : false);
        $photo->used_times = $usedTimes;

        if (class_exists('GridGalleryPro_Galleries_Model_Tags')) {
            $tags   = new GridGalleryPro_Galleries_Model_Tags();
            $result = $tags->getByPhotoId($photo->id);
            $photo->tags = array();

            if (is_object($result) && property_exists($result, 'tags')) {
                $photo->tags = explode(',', $result->tags);
            }
        }

        return $photo;
    }

    /**
     * Sets the environment.
     *
     * @param Rsc_Environment $environment
     */
    public function setEnvironment(Rsc_Environment $environment)
    {
        $this->environment = $environment;
    }
}
