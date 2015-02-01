<?php


abstract class SupsysticSlider_Ui_Asset
{

    /**
     * @var string
     */
    protected $handle;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var array
     */
    protected $deps;

    /**
     * @var int|string
     */
    protected $version;

    /**
     * @var bool
     */
    protected $preventCaching;

    /**
     * Constructor
     * @param string $handle
     * @param string $source
     * @param bool $preventCaching
     */
    public function __construct($handle, $source = null, $preventCaching = false)
    {
        $this->handle  = $handle;
        $this->source  = $source;
        $this->preventCaching = (bool) $preventCaching;
    }

    /**
     * Enqueue the asset
     */
    abstract public function enqueue();

    /**
     * Loads the asset
     */
    abstract public function load();

    /**
     * @param string $action
     */
    protected function register($action)
    {
        add_action($action, array($this, 'enqueue'));
    }

    /**
     * Sets the dependencies
     *
     * @param array $deps
     * @return SupsysticSlider_Ui_Asset
     */
    public function setDeps(array $deps)
    {
        $this->deps = $deps;
        return $this;
    }

    /**
     * Returns the dependencies
     *
     * @return array
     */
    public function getDeps()
    {
        return $this->deps;
    }

    /**
     * Sets the handle
     *
     * @param string $handle
     * @return SupsysticSlider_Ui_Asset
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
        return $this;
    }

    /**
     * Returns the handle
     *
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Sets the source
     *
     * @param string $source
     * @return SupsysticSlider_Ui_Asset
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Returns the source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Sets the version
     *
     * @param int|string $version
     * @return SupsysticSlider_Ui_Asset
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Returns the version
     *
     * @return int|string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Deny for browsers cache assets or not
     *
     * @param boolean $preventCaching
     * @return SupsysticSlider_Ui_Asset
     */
    public function setPreventCaching($preventCaching)
    {
        $this->preventCaching = (bool) $preventCaching;
        return $this;
    }

    /**
     * Returns the cache state
     *
     * @return boolean
     */
    public function getPreventCaching()
    {
        return $this->preventCaching;
    }
} 