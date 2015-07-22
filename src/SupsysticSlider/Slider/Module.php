<?php


class SupsysticSlider_Slider_Module extends SupsysticSlider_Core_BaseModule
{

    /** Config item with the available sliders. */
    const AVAILABLE_SLIDERS = 'plugin_sliders';

    /**
     * Module initialization.
     */
    public function onInit()
    {
        /** @var SupsysticSlider_Slider_Controller $controller */
        $controller = $this->getController();
        $dispatcher = $this->getEnvironment()->getDispatcher();
        $twig = $this->getEnvironment()->getTwig();

        $translate = new Twig_SimpleFunction('translate', array($this->getController(), 'translate'));
        $unserialize = new Twig_SimpleFunction('unserialize_twig', 'unserialize');
        $twig->addFunction($translate);
        $twig->addFunction($unserialize);

        // Loads module assets.
        $dispatcher->on('after_ui_loaded', array($this, 'loadAssets'));

        // Find all sliders after all modules has been loaded.
        $dispatcher->on('after_modules_loaded', array($this, 'findSliders'));

        // Load twig extensions.
        $dispatcher->on('after_modules_loaded', array($this, 'loadExtensions'));

        // If one of the photo will be removed from database
        // we'll remove it from the slider automatically.
        $dispatcher->on(
            'photos_delete_by_id',
            array($controller->getModel('resources'), 'deletePhotoById')
        );

        // Add shortcode
        add_shortcode('supsystic-slider', array($this, 'render'));

        add_action( 'widgets_init', array($this, 'registerWidget'));

        // Register menu items.
        $dispatcher->on(
            'ui_menu_items',
            array($this, 'addNewSliderMenuItem'),
            1,
            0
        );
    }

    /**
     * Loads module assets.
     * @param SupsysticSlider_Ui_Module $ui UI Module.
     */
    public function loadAssets(SupsysticSlider_Ui_Module $ui)
    {
        $environment = $this->getEnvironment();
        $preventCaching = $environment->isDev();

        if($environment->getPluginName() != 'ssl'){
            return;
        }

        if (!$environment->isModule('slider')) {
            return;
        }

        $ui->add(
            new SupsysticSlider_Ui_BackendJavascript(
                'supsysticSlider-slider-noty',
                $this->getLocationUrl() . '/assets/js/noty/js/noty/packaged/jquery.noty.packaged.min.js',
                $preventCaching
            )
        );

        $ui->add(
            new SupsysticSlider_Ui_BackendStylesheet(
                'supsysticSlider-slider-styles',
                $this->getLocationUrl() . '/assets/css/slider.css',
                $preventCaching
            )
        );

        if ($environment->isAction('index') || $environment->isAction('showPresets')) {
            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-index',
                    $this->getLocationUrl() . '/assets/js/index.js',
                    $preventCaching
                )
            );
        }

        if ($environment->isAction('add')) {
            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-add',
                    $this->getLocationUrl() . '/assets/js/add.js',
                    $preventCaching
                )
            );
        }

        if ($environment->isAction('view')) {

            $ui->add(
                new SupsysticSlider_Ui_BackendStylesheet(
                    'supsysticSlider-slider-stylesAnimate',
                    $this->getLocationUrl() . '/assets/css/animate.css',
                    $preventCaching
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendStylesheet(
                    'wp-color-picker'
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-frontend',
                    $this->getLocationUrl() . '/assets/js/frontend.js',
                    $preventCaching
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-settingsTabs',
                    $this->getLocationUrl() . '/assets/js/settings-tabs.js',
                    $preventCaching
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-settings',
                    $this->getLocationUrl() . '/assets/js/settings.js',
                    $preventCaching
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-view',
                    $this->getLocationUrl() . '/assets/js/view.js',
                    $preventCaching
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-viewToolbar',
                    $this->getLocationUrl() . '/assets/js/view-toolbar.js',
                    $preventCaching
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-sorting',
                    $this->getLocationUrl() . '/assets/js/sorting.js',
                    $preventCaching
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-preview',
                    $this->getLocationUrl() . '/assets/js/preview.js',
                    $preventCaching
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-lettering',
                    $this->getLocationUrl() . '/assets/js/jquery.lettering.js',
                    $preventCaching
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-texttillate',
                    $this->getLocationUrl() . '/assets/js/jquery.textillate.js',
                    $preventCaching
                )
            );

            $ui->add(
                new SupsysticSlider_Ui_BackendJavascript(
                    'supsysticSlider-slider-easing',
                    'http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js',
                    $preventCaching
                )
            );

            // Visual Editor.
            $veditor = new SupsysticSlider_Ui_BackendJavascript(
                'supsysticSlider-slider-veditor',
                $this->getLocationUrl() . '/assets/js/visual-editor.js',
                $preventCaching
            );

            $veditor->setDeps(array('wp-color-picker'));

            $ui->add($veditor);
        }
    }

    /**
     * Loads Twig extensions.
     */
    public function loadExtensions()
    {
        $twig = $this->getEnvironment()->getTwig();

        $twig->addExtension(new SupsysticSlider_Slider_Twig_Attachment());
    }

    public function enqueueFrontendJavascript() {
        $dispatcher = $this->getEnvironment()->getDispatcher();
        $dispatcher->dispatch('bx_enqueue_javascript');

        wp_enqueue_script(
            'supsysticSlider-slider-frontend',
            $this->getLocationUrl() . '/assets/js/frontend.js',
            array(),
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'supsysticSlider-slider-lettering',
            $this->getLocationUrl() . '/assets/js/jquery.lettering.js',
            array(),
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'supsysticSlider-slider-texttillate',
            $this->getLocationUrl() . '/assets/js/jquery.textillate.js',
            array(),
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'supsysticSlider-slider-easing',
            'http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js',
            array(),
            '1.0.0',
            true
        );

    }

    public function enqueueFrontendStylesheet() {
        wp_enqueue_style(
            'supsysticSlider-slider-stylesAnimateLetters',
            $this->getLocationUrl() . '/assets/css/animate.css',
            array(),
            '1.0.0',
            true
        );
    }

    public function render($attributes)
    {
        if (!isset($attributes['id'])) {
            // @TODO: Maybe we need to show error message here.
            return;
        }

        /** @var string|int $id */
        $id = $attributes['id'];
        /** @var SupsysticSlider_Slider_Controller $controller */
        $controller = $this->getController();
        /** @var SupsysticSlider_Slider_Model_Sliders $sliders */
        $sliders = $controller->getModel('sliders');
        $slider  = $sliders->getById((int)$id);
        $twig = $this->getEnvironment()->getTwig();

        if (!$slider) {
            // @TODO: Maybe we need to show error message here.
            return;
        }

        if (isset($attributes['width'])) {
            $slider->settings['properties']['width'] = $attributes['width'];
        }

        if (isset($attributes['height'])) {
            $slider->settings['properties']['height'] = $attributes['height'];
        }

		if (isset($attributes['position'])) {
			$slider->settings['properties']['position'] = $attributes['position'];
		}

        /** @var SupsysticSlider_Slider_Interface $module */
        $module = $this->getEnvironment()->getModule($slider->plugin);

        if (!$module) {
            return;
        }

        add_action('wp_footer', array($module, 'enqueueJavascript'));
        add_action('wp_footer', array($module, 'enqueueStylesheet'));

        add_action('wp_footer', array($this, 'enqueueFrontendJavascript'));
        add_action('wp_footer', array($this, 'enqueueFrontendStylesheet'));

        $posts = $this->getController()->getModel('settings')->getPosts($slider->id, 'full');

        /*$twig->addFunction(
            new Twig_SimpleFunction(
                'get_image_src',
                'wp_get_attachment_image_src'
            )
        );*/

        $slider->posts = $posts;

        //redo this
        foreach($slider->images as $key => $value) {
            $html = get_post_meta($value->attachment_id, 'slideHtml');

            if($html && !empty($html)) {
                $slider->images[$key]->attachment['html'] = $html[0];
            }
            if(defined('$slider->entities[$key]->url')) {
                $slider->entities[$key]->attachment['service'] = $this->getService($slider, $key);
            }
        }

        return $module->render($slider);
    }

    /**
     * Finds all modules that implement SupsysticSlider_Slider_Interface
     * and registers as sliders.
     */
    public function findSliders()
    {
        $environment = $this->getEnvironment();

        $config  = $environment->getConfig();
        $modules = $environment
            ->getResolver()
            ->getModulesList();

        if (!$config->has(self::AVAILABLE_SLIDERS)) {
            $config->add(self::AVAILABLE_SLIDERS, array());
        }

        $available = $config->get(self::AVAILABLE_SLIDERS);

        if ($modules->isEmpty()) {
            return;
        }

        foreach ($modules as $module) {
            if ($module instanceof SupsysticSlider_Slider_Interface) {
                $available[] = $module;
            }
        }

        $config->set(self::AVAILABLE_SLIDERS, $available);
    }

    /**
     * Returns array with the available sliders.
     *
     * @return SupsysticSlider_Slider_Info[]
     */
    public function getAvailableSliders()
    {
        return $this->getEnvironment()
            ->getConfig()
            ->get(
                self::AVAILABLE_SLIDERS,
                array()
            );
    }

    public function registerWidget() {
        register_widget( 'sslWidget' );
    }

    public function addNewSliderMenuItem()
    {
        $menu = $this->getEnvironment()->getMenu();

        $submenuNewSlider = $menu->createSubmenuItem();
        $submenuNewSlider->setCapability('manage_options')
            //->setMenuSlug('supsystic-slider&module=slider&action=index&add=true')
			->setMenuSlug('supsystic-slider&module=slider&action=showPresets')
            ->setMenuTitle('New Slider')
            ->setPageTitle('New Slider')
            ->setModuleName('slider');

        $menu->addSubmenuItem('newSlider', $submenuNewSlider);
//            ->register();

        $submenuSliders = $menu->createSubmenuItem();
        $submenuSliders->setCapability('manage_options')
            ->setMenuSlug('supsystic-slider&module=slider')
            ->setMenuTitle('Sliders')
            ->setPageTitle('Sliders')
            ->setModuleName('slider');

        $menu->addSubmenuItem('sliders', $submenuSliders);
    }

    protected function getService($slider, $index) {
        foreach (array('youtube', 'vimeo') as $service) {
            //$pattern = sprintf('/%s/i', $service);

            if (strpos($slider->entities[$index]->url, $service)) {
                return $service;
            }
        }
    }
}

require_once('Model/widget.php');
