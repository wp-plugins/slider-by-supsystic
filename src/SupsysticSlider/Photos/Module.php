<?php

/**
 * Class SupsysticSlider_Photos_Module
 * Photos module
 *
 * @package SupsysticSlider\Photos
 * @author Artur Kovalevsky
 */
class SupsysticSlider_Photos_Module extends Rsc_Mvc_Module
{

    /**
     * {@inheritdoc}
     */
    public function onInit()
    {
        parent::onInit();

        $config = $this->getEnvironment()->getConfig();

        add_action('admin_enqueue_scripts', array($this, 'enqueueMedia'));

        add_action($config->get('hooks_prefix') . 'after_ui_loaded', array($this, 'loadAssets'));

        add_action('delete_attachment', array(
            new SupsysticSlider_Photos_Model_Photos(
                $config->isEnvironment(Rsc_Environment::ENV_DEVELOPMENT)
            ), 'deleteByAttachmentId'
        ));

        add_action(
            'grid_gallery_delete_folder',
            array(
                new SupsysticSlider_Photos_Model_Photos($config->isEnvironment(
                    Rsc_Environment::ENV_DEVELOPMENT
                )),
                'deleteByFolderId'
            )
        );

        // Sets the JPEG quality.
        add_filter('jpeg_quality', array($this, 'getJpegQuality'));

        $dispatcher = $this->getEnvironment()->getDispatcher();

        $dispatcher->on(
            'ui_menu_items',
            array($this, 'registerMenuItem'),
            2,
            0
        );
    }

    /**
     * Loads WordPress Media API
     */
    public function enqueueMedia()
    {
        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }
    }

    /**
     * Loads the assets of the current module
     * @param SupsysticSlider_Ui_Module $ui
     */
    public function loadAssets(SupsysticSlider_Ui_Module $ui)
    {
        $ui->add(new SupsysticSlider_Ui_BackendJavascript('jquery'));
        $ui->add(new SupsysticSlider_Ui_BackendJavascript('jquery-ui-dialog'));

        $ui->add(new SupsysticSlider_Ui_BackendStylesheet(
            'gg-photos',
            $this->getLocationUrl() . '/assets/css/gird-gallery.photos.css'
        ));

        $ui->add(new SupsysticSlider_Ui_BackendJavascript(
            'gg-photos-js',
            $this->getLocationUrl() . '/assets/js/photos.js'
        ));

        // $ui->add(new SupsysticSlider_Ui_BackendJavascript(
        //     'gg-_photos-js',
        //     $this->getLocationUrl() . '/assets/js/_photos.js'
        // ));

        $ui->add(new SupsysticSlider_Ui_BackendJavascript('jquery-ui-draggable'));
        $ui->add(new SupsysticSlider_Ui_BackendJavascript('jquery-ui-droppable'));
        $ui->add(new SupsysticSlider_Ui_BackendJavascript('rs-uri', $this->getLocationUrl() . '/assets/js/URI.min.js'));
        $ui->add(new SupsysticSlider_Ui_BackendJavascript('rs-pos', $this->getLocationUrl() . '/assets/js/position.js'));
        $ui->add(new SupsysticSlider_Ui_BackendJavascript(
            'gg-uploader-js',
            $this->getLocationUrl() . '/assets/js/gird-gallery.photos.uploader.js'
        ));

        $ui->add(new SupsysticSlider_Ui_BackendJavascript(
            'gg-photos-folders-js',
            $this->getLocationUrl() . '/assets/js/gird-gallery.photos.folders.js'
        ));

    }

    /**
     * Returns the JPEG quality value.
     * If value is not specified: default WordPress values will be used (80%).
     * @return int
     */
    public function getJpegQuality()
    {
        $config = $this->getEnvironment()->getConfig();

        return $config->get('jpeg_quality', 80);
    }

    /**
     * Returns sorted array of the images.
     *
     * @param array $images
     * @return array
     */
    public function sortImages(array $images)
    {
        /** @var SupsysticSlider_Photos_Controller $controller */
        $controller = $this->getController();
        /** @var SupsysticSlider_Photos_Model_Position $position */
        $position   = $controller->getModel('position');

        $images = array_map(
            array($position, 'setPosition'),
            $images
        );

        return $position->sort($images);
    }

    public function registerMenuItem()
    {
        //Uncomment to register Images menu
        /*$menu = $this->getEnvironment()->getMenu();
        $submenu = $menu->createSubmenuItem();
        $submenu->setCapability('manage_options')
            ->setMenuSlug('supsystic-slider&module=photos')
            ->setMenuTitle('Images')
            ->setPageTitle('Images')
            ->setModuleName('photos');

        $menu->addSubmenuItem('photos', $submenu);
        ->register();*/
    }
}
