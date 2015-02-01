<?php

/**
 * Sliders model.
 */
class SupsysticSlider_Slider_Model_Sliders
    extends SupsysticSlider_Core_BaseModel
    implements Rsc_Environment_AwareInterface
{

    /** Query will return single row. */
    const MODE_ROW = 0;
    /** Query will return objects collection. */
    const MODE_COLLECTION = 1;
    /** Event name that will be trigger when the slider need to be compiled. */
    const EVENT_COMPILE = 'compile_slider';

    /**
     * @var Rsc_Environment
     */
    protected $environment;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable($this->db->prefix . 'rs_sliders');
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

    /**
     * Select slider by ID.
     *
     * @param int $id Slider ID.
     * @return object|null
     */
    public function getById($id)
    {
        return $this->getBy('id', (int) $id, self::MODE_ROW);
    }

    /**
     * Returns an array of the all sliders.
     * @return stdClass[]
     */
    public function getAll()
    {
        $query = $this->getQueryBuilder()
            ->select('*')
            ->from($this->getTable());

        $sliders = $this->db->get_results($query->build());

        if (is_array($sliders) && count($sliders) > 0) {
            return $this->compile($sliders);
        }

        return array();
    }

    /**
     * Deletes slider by id.
     *
     * @param int $id Slider Id.
     * @return bool
     */
    public function deleteById($id)
    {
        if ($this->deleteBy('id', (int) $id)) {
            return true;
        }

        return false;
    }

    /**
     * Creates new slider.
     *
     * @param string $title Slider title.
     * @param string $plugin Slider plugin.
     * @throws Exception
     * @return int Insert Id.
     */
    public function create($title, $plugin)
    {
        $title = htmlspecialchars($title, ENT_QUOTES, get_bloginfo('charset'));

        $query = $this->getQueryBuilder()
            ->insertInto($this->getTable())
            ->fields('title', 'plugin')
            ->values($title, $plugin);

        if (!$this->db->query($query->build())) {
            throw new Exception($this->db->last_error);
        }

        $insertId = $this->db->insert_id;
        $this->setInsertId($insertId);

        return $insertId;
    }

    /**
     * Universal sliders getter.
     *
     * @param string $field Field name.
     * @param string $value Value to search.
     * @param int    $mode Select mode.
     * @return object|null
     */
    public function getBy($field, $value, $mode = self::MODE_COLLECTION)
    {
        $result = null;
        $query  = $this->getQueryBuilder()
            ->select('*')
            ->from($this->getTable())
            ->where($field, '=', $value);

        switch ($mode) {
            case self::MODE_ROW:
                $result = $this->db->get_row($query->build());
                break;
            case self::MODE_COLLECTION:
                $result = $this->db->get_results($query->build());
                break;
            default:
                $result = null;
        }

        if ($this->environment) {
            $result = $this->compile($result);
        }

        return $result;
    }

    /**
     * Universal delete method.
     *
     * @param string $field Field name.
     * @param mixed $value Value to search.
     * @return false|int
     */
    public function deleteBy($field, $value)
    {
        $query = $this->getQueryBuilder()
            ->deleteFrom($this->getTable())
            ->where($field, '=', $value);

        return $this->db->query($query->build());
    }

    /**
     * Updates the settings id for the selected slider.
     *
     * @param int $sliderId Slider identifier.
     * @param int $settingsId Settings identifier.
     * @return bool
     */
    public function updateSettingsId($sliderId, $settingsId)
    {
        $query = $this->getQueryBuilder()
            ->update($this->getTable())
            ->where('id', '=', (int)$sliderId)
            ->fields('settings_id')
            ->values((int)$settingsId);

        if (!$this->db->query($query->build())) {
            $this->setLastError($this->db->last_error);

            return false;
        }

        return true;
    }

    /**
     * Updates plugin field for selected slider.
     *
     * @param int $sliderId Slider id.
     * @param string $plugin Plugin name.
     * @return bool
     */
    public function updatePlugin($sliderId, $plugin)
    {
        $query = $this->getQueryBuilder()
            ->update($this->getTable())
            ->where('id', '=', (int)$sliderId)
            ->fields('plugin')
            ->values($plugin);

        if (false === $this->db->query($query->build())) {
            $this->setLastError($this->db->last_error);

            return false;
        }

        return true;
    }

    /**
     * Extends the slider object with required properties.
     *
     * @param stdClass[]|stdClass $slider Slider object
     * @throws RuntimeException
     * @return stdClass[]|stdClass
     */
    public function compile($slider)
    {
        if (is_array($slider)) {
            return array_map(array($this, 'compile'), $slider);
        }

        if (!$this->environment) {
            throw new RuntimeException('Environment is not specified.');
        }

        $dispatcher = $this->environment->getDispatcher();

        return $dispatcher->apply(self::EVENT_COMPILE, array($slider));
    }
}