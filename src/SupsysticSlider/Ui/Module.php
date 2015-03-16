<?php

/**
 * Class SupsysticSlider_Ui_Module
 * User Interface Module
 *
 * @package SupsysticSlider\Ui
 * @author Artur Kovalevsky
 */
class SupsysticSlider_Ui_Module extends Rsc_Mvc_Module
{
    /**
     * @var array
     */
    protected $javascripts;

    /**
     * @var array
     */
    protected $stylesheets;

    /**
     * @var SupsysticSlider_Ui_AssetsCollection
     */
    protected $assets;

    /**
     * {@inheritdoc}
     */
    public function onInit()
    {
        parent::onInit();

        $this->assets = new SupsysticSlider_Ui_AssetsCollection();

        $this->preload();

        $config = $this->getEnvironment()->getConfig();

        add_action(
            $config->get('hooks_prefix') . 'after_modules_loaded',
            array($this->assets, 'load')
        );

        add_action('admin_enqueue_scripts', array($this, 'colorpicker'));

        // Allows to sort menu items.
        $dispatcher = $this->getEnvironment()->getDispatcher();

        $dispatcher->dispatch('ui_menu_items');
        $this->getEnvironment()->getMenu()->register();
    }

    public function colorpicker()
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('rs-color-picker', $this->getLocationUrl() . '/js/colorpicker.js', array('wp-color-picker'));
    }

    /**
     * Adds the asset
     * @param SupsysticSlider_Ui_Asset $asset
     */
    public function add(SupsysticSlider_Ui_Asset $asset)
    {
        $this->assets->add($asset);
    }

    /**
     * Returns the asset if it exists.
     * @param string $handle
     * @param mixed $default
     * @return SupsysticSlider_Ui_Asset
     */
    public function get($handle, $default = null)
    {
        return $this->assets->get($handle, $default);
    }

    /**
     * Deletes the asset.
     * @param string $handle
     * @return bool
     */
    public function delete($handle)
    {
        return $this->assets->delete($handle);
    }

    /**
     * Preloads the assets
     */
    protected function preload()
    {
        /* URL to the plugin folder */
        $url = $this->getEnvironment()->getConfig()->get('plugin_url');

        /* CSS */
        $this->add(new SupsysticSlider_Ui_BackendStylesheet('rs-ui', $url . '/app/assets/css/supsystic-ui.css'));
        $this->add(new SupsysticSlider_Ui_BackendStylesheet('jqgrid-ui', $url . '/app/assets/css/ui.jqgrid.css'));
        $this->add(new SupsysticSlider_Ui_BackendStylesheet('rs-jquery-min-ui', $url.'/app/assets/css/jquery-ui.min.css'));
        $this->add(new SupsysticSlider_Ui_BackendStylesheet('rs-jquery-structure-min-ui', $url.'/app/assets/css/jquery-ui.structure.min.css'));
        $this->add(new SupsysticSlider_Ui_BackendStylesheet('rs-jquery-theme-min-ui', $url.'/app/assets/css/jquery-ui.theme.min.css'));
        $this->add(new SupsysticSlider_Ui_BackendStylesheet('rs-font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css'));
        $this->add(new SupsysticSlider_Ui_BackendStylesheet('rs-jgrowl', '//cdnjs.cloudflare.com/ajax/libs/jquery-jgrowl/1.2.12/jquery.jgrowl.min.css'));
        $this->add(new SupsysticSlider_Ui_BackendStylesheet('rs-tooltipster', '//cdn.jsdelivr.net/jquery.tooltipster/3.3.0/css/tooltipster.css'));
        $this->add(new SupsysticSlider_Ui_BackendStylesheet('rs-tooltipster-theme-shadow', '//cdn.jsdelivr.net/jquery.tooltipster/3.3.0/css/themes/tooltipster-shadow.css'));
        $this->add(new SupsysticSlider_Ui_Stylesheet('rs-shadows-css', $url . '/app/assets/css/shadows.css'));
        $this->add(new SupsysticSlider_Ui_BackendStylesheet('rs-shadows-backend-css', $url . '/app/assets/css/shadows.css'));

        /* Javascript */
        $this->add(new SupsysticSlider_Ui_BackendJavascript('jquery'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('jquery-ui-dialog'));

        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-types', $this->getLocationUrl() . '/js/types.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-ui-js', $url . '/app/assets/js/gird-gallery.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-lazy-load-ks', $url . '/app/assets/js/jquery.lazyload.min.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-form-serializer-js', $this->getLocationUrl() . '/plugins/gird-gallery.ui.formSerialize.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-jgrowl-js', '//cdnjs.cloudflare.com/ajax/libs/jquery-jgrowl/1.2.12/jquery.jgrowl.min.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-tooltipster-js', '//cdn.jsdelivr.net/jquery.tooltipster/3.3.0/js/jquery.tooltipster.min.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('gg-ddslick-js', '//cdn.jsdelivr.net/ddslick/2.0/jquery.ddslick.min.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-slimscroll-js', $this->getLocationUrl() . '/js/jquery.slimscroll.min.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-jqgrid-min-js', '//cdn.jsdelivr.net/jqgrid/4.6.0/jquery.jqGrid.min.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-jqgrid-locale-en-js', '//cdn.jsdelivr.net/jqgrid/4.6.0/i18n/grid.locale-en.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-toolbar-js', $this->getLocationUrl() . '/plugins/gird-gallery.ui.toolbar.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-cb-observ', $this->getLocationUrl() . '/js/checkbox-observer.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-ui-toolbar', $this->getLocationUrl() . '/js/toolbar.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-common', $this->getLocationUrl() . '/js/common.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-ajax', $this->getLocationUrl() . '/js/ajax.js'));
        $this->add(new SupsysticSlider_Ui_BackendJavascript('rs-ajax-queue', $this->getLocationUrl() . '/js/ajaxQueue.js'));
    }

}
