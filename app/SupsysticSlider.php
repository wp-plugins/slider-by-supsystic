<?php

/**
 * Class SupsysticSlider
 */
class SupsysticSlider
{
    /**
     * @var Rsc_Environment
     */
    private $environment;

    /**
     * @var array
     */
    private $alerts;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!class_exists('Rsc_Autoloader', false)) {
            require dirname(dirname(__FILE__)) . '/vendor/Rsc/Autoloader.php';
            Rsc_Autoloader::register();
        }

        add_action('init', array($this, '_loadPluginsTextdomain'));
        add_action('init', array($this, 'addShortcodeButton'));

        /* Create new plugin instance */
        $pluginPath  = dirname(dirname(__FILE__));
        $environment = new Rsc_Environment('ssl', '1.3.4', $pluginPath);

        /* Configure */
        $environment->configure(
            array(
                'environment'      => $this->getPluginEnvironment(),
                'default_module'   => 'slider',
                'default_slider'   => 'bx',
                'lang_domain'      => 'supsystic-slider',
                'lang_path'        => plugin_basename(dirname(__FILE__)) . '/langs',
                'plugin_prefix'    => 'SupsysticSlider',
                'plugin_source'    => dirname(dirname(__FILE__)) . '/src',
                'plugin_menu'      => array(
                    'page_title' => __('Slider by Supsystic', 'supsystic-slider'),
                    'menu_title' => __('Slider by <br/> Supsystic', 'supsystic-slider'),
                    'capability' => 'manage_options',
                    'menu_slug'  => 'supsystic-slider',
                    'icon_url'   => 'dashicons-format-gallery',
                    'position'   => '100.4',
                ),
                'shortcode_name'   => 'supsystic-slider',
                'db_prefix'        => 'rs_',
                'hooks_prefix'     => 'ss_',
                'page_url'         => 'http://supsystic.com/plugins/slider',
                'ajax_url'         => admin_url('admin-ajax.php'),
                'ajax_action'      => 'supsystic-slider',
                'admin_url'        => admin_url(),
                'uploads_rw'       => true,
                'jpeg_quality'     => 95,
                'plugin_db_update' => true,
                'revision'         => 235,
            )
        );

        $this->environment = $environment;
        $this->alerts      = array();

        $this->initialize();
        
        if (!defined('S_YOUR_SECRET_HASH_'. $environment->getPluginName())) {
            define(
                'S_YOUR_SECRET_HASH_' . $environment->getPluginName(),
                'hn48SgUyMN53#jhg7@pomnE9W2O#2m@awmMneuGW3512F@jnkj'
            );
        }
    }

    /**
     * Run plugin
     */
    public function run()
    {
        $this->environment->run();
        $this->environment->getTwig()->addGlobal('core_alerts', $this->alerts);
    }

    public function getEnvironment() {

        return $this->environment;
    }

    public function _loadPluginsTextDomain()
    {
        load_plugin_textdomain(
            'ssl',
            false,
            'slider-by-supsystic/app/langs/'
        );
    }


    public function addShortcodeButton() {
        add_filter( "mce_external_plugins", array($this, 'addButton'));
        add_filter( 'mce_buttons', array($this, 'registerButton'));
        if(is_admin()) {
            wp_enqueue_script('ssl-bpopup-js', $this->environment->getConfig()->get('plugin_url') . '/app/assets/js/jquery.bpopup.min.js');
            wp_enqueue_style('ssl-popup-css', $this->environment->getConfig()->get('plugin_url') . '/app/assets/css/editor-dialog.css');
        }
    }

    public function addButton( $plugin_array ) {
        $plugin_array['addShortcodeSlider'] = $this->environment->getConfig()->get('plugin_url') . '/app/assets/js/buttons.js';

        return $plugin_array;
    }
    public function registerButton( $buttons ) {
        array_push( $buttons, 'addShortcodeSlider', 'selectShortcode' );

        return $buttons;
    }

    protected function initialize()
    {
        $config = $this->environment->getConfig();
        $logger = null;

        $uploads = wp_upload_dir();

        if (!is_writable($uploads['basedir'])) {
            $this->alerts[] = sprintf(
                '<div class="error">
                    <p>You need to make your "%s" directory writable.</p>
                </div>',
                $uploads['basedir']
            );

            $config->set('uploads_rw', false);
        }

        /* Create the plugin directories if they are does not exists yet. */
        $this->initFilesystem();

        /* Initialize cache null-adapter by default */
        $cacheAdapter = new Rsc_Cache_Dummy();

        /* Initialize the log system first. */
        if (null !== $logDir = $config->get('plugin_log', null)) {
            if (is_dir($logDir) && is_writable($logDir)) {
                $logger = new Rsc_Logger($logDir);
                $this->environment->setLogger($logger);
            }
        }

        /* If it's a production environment and cache directory is OK */
        if ($config->isEnvironment(Rsc_Environment::ENV_PRODUCTION)
            && null !== $cacheDir = $config->get('plugin_cache', null)
        ) {
            if (is_dir($cacheDir) && is_writable($cacheDir)) {
                $cacheAdapter = new Rsc_Cache_Filesystem($cacheDir);
            } else {
                if ($logger) {
                    $logger->error(
                        'Cache directory "{dir}" is not writable or does not exists.',
                        array(
                            'dir' => realpath($cacheDir),
                        )
                    );
                }
            }
        }

        $this->environment->setCacheAdapter($cacheAdapter);
    }

    /**
     * Creates plugin's directories.
     */
    protected function initFilesystem()
    {
        $directories = array(
            'tmp'        => '/supsystic-slider',
            'log'        => '/supsystic-slider/log',
            'cache'      => '/supsystic-slider/cache',
            'twig_cache' => '/supsystic-slider/cache/twig',
        );

        foreach ($directories as $key => $dir) {
            if (false !== $fullPath = $this->makeDirectory($dir)) {
                $this->environment->getConfig()->add('plugin_' . $key, $fullPath);
            }
        }
    }

    /**
     * Make directory in uploads directory.
     * @param string $directory Relative to the WP_UPLOADS dir
     * @return bool|string FALSE on failure, full path to the directory on success
     */
    protected function makeDirectory($directory)
    {
        $uploads = wp_upload_dir();
        $basedir = $uploads['basedir'];

        if (!is_dir($dir = $basedir . $directory)) {
            if (false === @mkdir($dir, 0777, true)) {
                return false;
            }
        }

        return $dir;
    }

    protected function getPluginEnvironment()
    {
        $environment = Rsc_Environment::ENV_PRODUCTION;

        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (defined('SUPSYSTIC_SLIDER_DEBUG') && SUPSYSTIC_SLIDER_DEBUG) {
                $environment = Rsc_Environment::ENV_DEVELOPMENT;
            }
        }

        if ($_SERVER['SERVER_NAME'] === 'localhost' && $_SERVER['SERVER_PORT'] === '8001') {
            $environment = Rsc_Environment::ENV_DEVELOPMENT;
        }

        return $environment;
    }
}