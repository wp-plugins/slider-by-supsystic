<?php

/**
 * Coin-Slider module.
 *
 * Allows to use Coin-Slider as Slider by Supsystic module.
 */
class SupsysticSlider_Coin_Module extends Rsc_Mvc_Module implements SupsysticSlider_Slider_Interface
{

    const OPT_TRUE  = 'true';
    const OPT_FALSE = 'false';

    /**
     * Module initialization.
     * Loads assets and registers current module as slider.
     */
    public function onInit()
    {
        $dispatcher = $this->getEnvironment()->getDispatcher();

        // Load module assets.
        $dispatcher->on('after_ui_loaded', array($this, 'loadAssets'));
    }

    /**
     * Loads plugin assets.
     *
     * @param SupsysticSlider_Ui_Module $ui
     */
    public function loadAssets(SupsysticSlider_Ui_Module $ui)
    {
        $environment = $this->getEnvironment();

        // Disallow to cache assets in development environment.
        $preventCaching = $environment->isDev();

        // Load assets only on plugin pages.
        if (!$environment->isPluginPage()) {
            return;
        }

        $ui->add(
            new SupsysticSlider_Ui_BackendJavascript(
                'supsysticSlider-coin-js',
                $this->getLocationUrl() . '/assets/js/coin-slider.min.js',
                $preventCaching
            )
        );

        $ui->add(
            new SupsysticSlider_Ui_BackendStylesheet(
                'supsysticSlider-coin-style',
                $this->getLocationUrl() . '/assets/css/coin-slider-styles.css',
                $preventCaching
            )
        );

        if ($environment->isModule('slider', 'view')) {
            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-coin-settings',
                    $this->getLocationUrl() . '/assets/js/settings.js',
                    $preventCaching
                )
            );
        }

        $ui->add(
            new SupsysticSlider_Ui_BackendJavascript(
                'supsysticSlider-bx-preview',
                $this->getLocationUrl() . '/assets/js/frontend.js',
                $preventCaching
            )
        );
    }

    /**
     * Returns default slider settings.
     *
     * @return array
     */
    public function getDefaults()
    {
        return array(
            'effects'  => array(
                'effect'     => 'random',
                'titleSpeed' => 500,
                'opacity'    => 0.7,
                'delay'      => 3000,
                'hoverPause' => self::OPT_TRUE,
            ),
            'controls' => array(
                'navigation' => self::OPT_TRUE,
                'links'      => self::OPT_FALSE,
            ),
            'properties' => array(
                'width'  => 400,
                'height' => 240,
            ),
        );
    }

    public function getPresetSettings($presetName) {
        return $this->getDefaults();
    }

    /**
     * Renders specified slider and returns markup.
     *
     * @param object $slider Slider.
     * @return string
     */
    public function render($slider)
    {
        $twig = $this->getEnvironment()->getTwig();

        return $twig->render(
            '@coin/markup.twig',
            array(
                'slider' => $slider
            )
        );
    }

    /**
     * Returns slider name.
     *
     * @return string
     */
    public function getSliderName()
    {
        return 'Coin Slider';
    }

    /**
     * Enqueue javascript.
     */
    public function enqueueJavascript()
    {
        wp_enqueue_script('jquery');

        wp_enqueue_script(
            'supsysticSlider-coinSliderPlugin',
            $this->getLocationUrl() . '/assets/js/coin-slider.min.js',
            array(),
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'supsysticSlider-coinSlider-frontend',
            $this->getLocationUrl() . '/assets/js/frontend.js',
            array(),
            '1.0.0',
            true
        );
    }

    public function getSettingsTemplate()
    {
        return '@coin/settings.twig';
    }

    public function getSliderTemplate()
    {
        return '@coin/markup.twig';
    }

    /**
     * Enqueue stylesheet.
     */
    public function enqueueStylesheet()
    {
        wp_enqueue_style(
            'supsysticSlider-coinSliderPluginStyles',
            $this->getLocationUrl() . '/assets/css/coin-slider-styles.css',
            array(),
            '1.0.0',
            'all'
        );
    }

    /**
     * Is this slider available to use in free version.
     *
     * @return bool
     */
    public function isFree()
    {
        return true;
    }
}