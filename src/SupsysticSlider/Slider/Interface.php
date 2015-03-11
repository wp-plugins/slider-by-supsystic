<?php

/**
 * Describes slider interface.
 * Each module that loads slider to the plugin MUST implement current interface.
 * If module implements that interface then it will be loaded as slider automatically.
 */
interface SupsysticSlider_Slider_Interface
{
    /**
     * Returns default slider settings.
     *
     * @return array
     */
    public function getDefaults();

    /**
     * Returns slider name.
     *
     * @return string
     */
    public function getSliderName();

    /**
     * Enqueue javascript.
     */
    public function enqueueJavascript();

    /**
     * Enqueue stylesheet.
     */
    public function enqueueStylesheet();

    /**
     * Is this slider available to use in free version.
     *
     * @return bool
     */
    public function isFree();

    /**
     * Renders specified slider and returns markup.
     *
     * @param object $slider Slider.
     * @return string
     */
    public function render($slider);

    /**
     * Returns path to the settings template.
     *
     * @return string
     */
    public function getSettingsTemplate();

    /**
     * Returns path to the template which be used with short code.
     *
     * @return string
     */
    public function getSliderTemplate();

    /**
     * Return specified preset settings
     *
     * */
    public function getPresetSettings($presetName);
} 