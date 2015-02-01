<?php

/**
 * Class SupsysticSlider_Colorbox_Module.
 * Registers the Colorbox in the system.
 * @package SupsysticSlider\Colorbox
 */
class SupsysticSlider_Colorbox_Module extends Rsc_Mvc_Module
{

    /**
     * {@inheritdoc}
     */
    public function onInit()
    {
        parent::onInit();
        add_action('gg_after_ui_loaded', array($this, 'load'));
    }

    /**
     * Loads the Colorbox's plugin, locale and theme after the UI module loaded.
     * @param SupsysticSlider_Ui_Module $ui The UI Module.
     */
    public function load(SupsysticSlider_Ui_Module $ui)
    {
        $this->loadPlugin($ui);
        $this->loadLocale($ui);
        $this->loadTheme($ui);
    }

    /**
     * Reloads the Colorbox module.
     * @return bool
     */
    public function reload()
    {
        $ui = $this->getEnvironment()->getModule('ui');

        if (is_object($ui) && $ui instanceof SupsysticSlider_Ui_Module) {
            $this->load($ui);

            return true;
        }

        return false;
    }

    /**
     * Returns the url to the jQuery plugin.
     * If is production environment, then will be returned the compressed
     * version of the plugin.
     *
     * @return string
     */
    protected function getPluginUrl()
    {
        $filename = 'jquery.colorbox.js';

        if ($this->isProduction()) {
            $filename = 'jquery.colorbox-min.js';
        }

        return $this->getLocationUrl() . '/jquery-colorbox/' . $filename;
    }

    /**
     * Checks whether the current environment is "production".
     * @return bool
     */
    protected function isProduction()
    {
        return $this->getEnvironment()->isProd();
    }

    /**
     * Loads the jQuery plugin to the Wordpress backend and frontend.
     * @param SupsysticSlider_Ui_Module $ui The UI Module.
     */
    protected function loadPlugin(SupsysticSlider_Ui_Module $ui)
    {
        $ui->add(
            new SupsysticSlider_Ui_BackendJavascript(
                'colorbox-backend-js',
                $this->getPluginUrl(),
                !$this->isProduction()
            )
        );

        $frontend = new SupsysticSlider_Ui_Javascript(
            'colorbox-frontend-js',
            $this->getPluginUrl(),
            !$this->isProduction()
        );

        $frontend->setDeps(array('jquery'));

        $ui->add($frontend);
    }

    /**
     * Loads the translation for the colorbox.
     * @param SupsysticSlider_Ui_Module $ui The UI Module.
     */
    protected function loadLocale(SupsysticSlider_Ui_Module $ui)
    {
        $locale = get_locale();
        $locale = strtolower($locale);

        if (in_array($locale, array('en_us', 'en_gb'))) {
            return;
        }

        $config = $this->getEnvironment()->getConfig();
        $config->load('@colorbox/parameters.php');

        $colorbox = $config->get('colorbox');

        if (!isset($colorbox['languages'][$locale])) {
            return;
        }

        $filename = '/jquery-colorbox/i18n/' . $colorbox['languages'][$locale];

        $ui->add(
            new SupsysticSlider_Ui_BackendJavascript(
                'colorbox-backend-lang',
                $this->getLocationUrl() . $filename,
                !$this->isProduction()
            )
        );

        $ui->add(
            new SupsysticSlider_Ui_Javascript(
                'colorbox-frontend-lang',
                $this->getLocationUrl() . $filename,
                !$this->isProduction()
            )
        );
    }

    /**
     * Loads the colorbox theme, specified in the configuration file to the
     * plugin backend.
     * @param SupsysticSlider_Ui_Module $ui The UI Module.
     */
    protected function loadTheme(SupsysticSlider_Ui_Module $ui)
    {
        $config = $this->getEnvironment()->getConfig();

        if (!$config->has('colorbox')) {
            $config->load('@colorbox/parameters.php');
        }

        $colorbox = $config->get('colorbox');

        $theme = (isset($colorbox['theme']) ? $colorbox['theme'] : 'theme_1');

        $filename = sprintf('/jquery-colorbox/themes/%s/colorbox.css', $theme);

        $ui->add(
            new SupsysticSlider_Ui_BackendStylesheet(
                'colorbox-backend-stylesheet',
                $this->getLocationUrl() . $filename
            )
        );
    }

    public function loadUserTheme($theme)
    {
        $filename = sprintf('/jquery-colorbox/themes/%s/colorbox.css', $theme);

        $ui = $this->getEnvironment()->getModule('ui');
        $ui->add(
            new SupsysticSlider_Ui_Stylesheet(
                'colorbox-backend-stylesheet',
                $this->getLocationUrl() . $filename
            )
        );
    }

    /**
     * Returns the full URL to the theme screenshot.
     * @param  string $themeName Theme name (theme_1, theme_2, etc).
     * @return string
     */
    public function getThemeScreenshotUrl($themeName)
    {
        $default  = 'http://placehold.it/262x213&text=No+image';
        $filename = $themeName . '.png';
        $url      = $this->getLocationUrl() . '/images/';

        if (!is_file(dirname(__FILE__) . '/images/' . $filename)) {
            return $default;
        }

        return $url . $filename;
    }
}
