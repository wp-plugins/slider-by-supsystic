<?php

/**
 * Handles sliders settings.
 */
class SupsysticSlider_Slider_Model_Settings extends SupsysticSlider_Core_BaseModel implements Rsc_Environment_AwareInterface
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable($this->db->prefix . 'rs_settings_sets');
    }

    /**
     * Returns settings by their id.
     *
     * @param int $id Settings identifier.
     * @return object|null
     */
    public function getById($id)
    {
        $query = $this->getQueryBuilder()
            ->select('*')
            ->from($this->getTable())
            ->where('id', '=', (int)$id);

        $settings = $this->db->get_row($query->build());

        if (!$settings) {
            return null;
        }

        $settings->data = unserialize($settings->data);

        return $settings;
    }

    /**
     * Stores settings in the database.
     *
     * @param array $data An array of the settings
     * @return bool
     */
    public function insert(array $data)
    {
        $query = $this->getQueryBuilder()
            ->insertInto($this->getTable())
            ->fields('data')
            ->values(serialize($data));

        if (!$this->db->query($query->build())) {
            $this->setLastError($this->db->last_error);

            return false;
        }

        $insertId = $this->db->insert_id;
        $this->setInsertId($insertId);

        return true;
    }

    /**
     * Updates selected settings.
     *
     * @param int $id Settings identifier.
     * @param array $data An array of the settings.
     * @return bool
     */
    public function update($id, array $data)
    {
        $query = $this->getQueryBuilder()
            ->update($this->getTable())
            ->where('id', '=', (int)$id)
            ->fields('data')
            ->values(serialize($data));

        if (false === $this->db->query($query->build())) {
            $this->setLastError($this->db->last_error);

            return false;
        }

        return true;
    }

    public function savePost($id, $sliderId = '', $type='post') {
        $elements = array();

        $elements = get_option($type . $sliderId);
        $elements = unserialize($elements);
        if(!empty($elements) && $elements) {
            if(!in_array($id, $elements)) {
                array_push($elements, $id);
            }
        } else {
            $elements = array($id);
        }
        $elements = serialize($elements);

        update_option($type . $sliderId, $elements);
    }

    public function getPosts($sliderId = '', $type='post', $thumbSize = 'thumbnail') {
        $elements = get_option($type . $sliderId);
        $elements = unserialize($elements);
        $posts = array();

        foreach($elements as $id) {
            $post = get_post($id);
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($id), $thumbSize);
            array_push($posts, array(
                'id' => $id,
                'title'=> $post->post_title,
                'image' => $image[0],
                'author' => the_author($id),
                'content' => $post->post_content

            ));
        }

        return $posts;
    }

    /**
     * Adds the settings to the slider object.
     *
     * @param object $slider
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     * @return object
     */
    public function getSliderSettings($slider)
    {
        if (!is_object($slider)) {
            throw new InvalidArgumentException(sprintf(
                'Parameter 1 must be an object, %s given.',
                gettype($slider)
            ));
        }

        if (!property_exists($slider, 'settings_id')) {
            throw new InvalidArgumentException('Invalid slider object given.');
        }

        $plugin = $slider->plugin;
        /** @var SupsysticSlider_Slider_Interface $module */
        $module = $this->environment->getModule($plugin);

        if ($slider->settings_id == 0) {

            if (null === $module) {
                return $slider;
            }

            if (!$module instanceof SupsysticSlider_Slider_Interface) {
                throw new LogicException(sprintf(
                    'Instance of %s must implement SupsysticSlider_Slider_Interface.',
                    get_class($module)
                ));
            }

            $slider->settings = $module->getDefaults();

            return $slider;
        }

        $settings = $this->getById($slider->settings_id);

        if (!$settings) {
            return $slider;
        }

        $slider->settings = array_merge(
            $module->getDefaults(),
            $settings->data
        );

        return $slider;
    }

    public function getImagesPath() {
        return get_bloginfo('wpurl'). '/wp-content/plugins/supsystic_slider/src/SupsysticSlider/Bx/assets/images';
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