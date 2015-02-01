<?php

/**
 * Class SupsysticSlider_Slider_Info
 */
class SupsysticSlider_Slider_Info
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var bool
     */
    private $free;

    /**
     * Constructor.
     *
     * @param string $name Slider name.
     * @param string $moduleName Module name.
     * @param bool $free Allow to use in free version or not.
     */
    public function __construct($name, $moduleName, $free)
    {
        $this->name       = $name;
        $this->moduleName = $moduleName;
        $this->free       = $free;
    }

    /**
     * @param string $moduleName Module name.
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = (string)$moduleName;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @param boolean $free
     */
    public function setFree($free)
    {
        $this->free = (bool)$free;
    }

    /**
     * @return boolean
     */
    public function getFree()
    {
        return $this->free;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string)$name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


} 