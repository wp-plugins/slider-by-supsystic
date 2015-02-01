<?php

/**
 * Class SupsysticSlider_Photos_Model_Folders
 * Folders
 *
 * @package SupsysticSlider\Photos\Model
 * @author Artur Kovalevsky
 */
class SupsysticSlider_Photos_Model_Folders extends Rsc_Mvc_Model
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var bool
     */
    protected $debugEnabled;

    /**
     * @var string
     */
    protected $lastError;

    /**
     * @var int
     */
    protected $insertId;

    /**
     * Constructor
     */
    public function __construct($debugEnabled = false)
    {
        parent::__construct();

        $this->table = $this->db->prefix . 'rs_folders';
        $this->debugEnabled = (bool) $debugEnabled;
    }

    /**
     * Sets the debug mode
     * @param boolean $debugEnabled
     * @return SupsysticSlider_Photos_Model_Folders
     */
    public function setDebugEnabled($debugEnabled)
    {
        $this->debugEnabled = (bool) $debugEnabled;
        return $this;
    }

    /**
     * Returns the last error
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Returns the insert id of the last insert query
     * @return int
     */
    public function getInsertId()
    {
        return $this->insertId;
    }

    /**
     * Adds the new album to the database
     * @param string $title The title of the album
     * @return bool TRUE on success, FALSE otherwise
     */
    public function add($title)
    {
        $title = htmlspecialchars($title, ENT_QUOTES, get_bloginfo('charset'));
        $query = $this->getQueryBuilder()->insertInto($this->table)
            ->fields('title', 'date')
            ->values($title, date('Y-m-d H:i:s'));

        if (!$this->db->query($query->build())) {
            $this->lastError = $this->db->last_error;
            return false;
        }

        $this->insertId = $this->db->insert_id;
        return true;
    }

    /**
     * Updates the title of the folder
     * @param int $id The identifier of the folder
     * @param string $title New title
     * @return bool TRUE on success, FALSE otherwise
     */
    public function updateTitle($id, $title)
    {
        $query = $this->getQueryBuilder()->update($this->table)
            ->fields(array('title'))
            ->values(array($title))
            ->where('id', '=', (int) $id);

        if (false === $this->db->query($query->build())) {
            return false;
        }

        return true;
    }

    /**
     * Returns the folder data by the identifier
     * @param int $id The identifier of the folder
     * @return object|null
     */
    public function getById($id)
    {
        $query = $this->getQueryBuilder()->select('*')
            ->from($this->table)
            ->where('id', '=', (int)$id);

        if ($folder = $this->db->get_row($query->build())) {
            return $this->extend($folder);
        }

        return null;
    }

    /**
     * Returns the array of the photos in specified folder
     * @param int $folderId The identifier of the folder
     * @return array|null
     */
    public function getPhotosById($folderId)
    {
        if (!class_exists('SupsysticSlider_Photos_Model_Photos', false)) {
            if ($this->debugEnabled) {
                wp_die (sprintf('The required class \'SupsysticSlider_Photos_Model_Photos\' is does not exists in method', __METHOD__));
            }

            return null;
        }

        $photos = new SupsysticSlider_Photos_Model_Photos($this->debugEnabled);

        return $photos->getPhotosByFolderId($folderId);
    }

    /**
     * Extends slider object with photos from folders.
     *
     * @param object $slider Slider object
     * @throws InvalidArgumentException
     * @return object
     */
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
            return $slider;
        }

        if (!is_array($slider->resources)) {
            throw new InvalidArgumentException(sprintf(
                'The "resources" property must be an array, %s given.',
                gettype($slider->resources)
            ));
        }

        foreach ($slider->resources as $resource) {
            if ($resource->resource_type === 'folder') {
                $folder = $this->getPhotosById($resource->resource_id);

                foreach ($folder as $index => $image) {
                    $image->rid = $resource->id;
                    $image->type = 'image';

                    $folder[$index] = $image;
                }

                $images = array_merge($images, $folder);

                if (class_exists('SupsysticSliderPro_Photos_Model_Videos')) {
                    $videos = new SupsysticSliderPro_Photos_Model_Videos();

                    $folder = $videos->getFromFolder($resource->resource_id);
                    if (is_array($folder) && count($folder) > 0) {
                        foreach ($folder as $index => $video) {
                            $video->rid  = $resource->id;
                            $video->type = 'video';

                            $folder[$index] = $video;
                        }

                        if (property_exists($slider, 'videos')) {
                            $slider->videos = array_merge(
                                $slider->videos,
                                $folder
                            );
                        } else {
                            $slider->videos = $folder;
                        }
                    }
                }
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

        return $slider;
    }

    /**
     * Returns all albums
     * @return array|null
     */
    public function getAll()
    {
        $query = $this->getQueryBuilder()->select('*')
            ->from($this->table);

        if ($folders = $this->db->get_results($query->build())) {
            foreach ($folders as $index => $folder) {
                $folders[$index] = $this->extend($folder);
            }
        }

        return $folders;
    }

    /**
     * Deletes the folders by the specified identifier
     * @param int $id The identifier of the album
     * @return bool TRUE on success, FALSE otherwise
     */
    public function deleteById($id)
    {
        $query = $this->getQueryBuilder()->deleteFrom($this->table)
            ->where('id', '=', (int) $id);

        if (!$this->db->query($query->build())) {
            return false;
        }

        do_action('grid_gallery_delete_folder', $id);

        return true;
    }

    /**
     * Extends the default database result of the folder
     * @param object|array $folder The database result of folder selection
     * @return object
     */
    protected function extend($folder)
    {
        if (!is_object($folder) && !is_array($folder)) {
            if ($this->debugEnabled) {
                wp_die ('Invalid $folder parameter specified');
            }
        }

        $folder = (object) $folder;

        $datetime = new DateTime($folder->date);

        $folder->date = $datetime->format(get_option('date_format'));
        $folder->photos = $this->getPhotosById($folder->id);

        return $folder;
    }
}
