<?php

/**
 * Class SupsysticSlider_Core_Module
 * Core module
 *
 * @package SupsysticSlider\Core
 * @author Artur Kovalevsky
 */
class SupsysticSlider_Core_Module extends Rsc_Mvc_Module
{

    /**
     * {@inheritdoc}
     */
    public function onInit()
    {
        parent::onInit();
        $path = dirname(dirname(dirname(dirname(__FILE__))));
        $url = plugins_url(basename($path));
        $config = $this->getEnvironment()->getConfig();

        $config->add('plugin_url', $url);
        $config->add('plugin_path', $path);

        add_filter('gg_hooks_prefix', array($this, 'addHooksPrefix'), 10, 1);
    }

    /**
     * Adds the plugin's hooks prefix to the hook name
     *
     * @param string $hook The name of the hook
     * @return string
     */
    public function addHooksPrefix($hook)
    {
        $config = $this->getEnvironment()->getConfig();

        return $config->get('hooks_prefix') . $hook;
    }

    public function afterUiLoaded(Callable $callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('$callback must be a callable');
        }

        add_action($this->addHooksPrefix('after_ui_loaded'), $callback);
    }
}
