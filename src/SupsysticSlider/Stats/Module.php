<?php

class SupsysticSlider_Stats_Module extends Rsc_Mvc_Module
{

    /**
     * @var GirdGallery_Stats_Model
     */
    private $stats;

    /**
     * {@inheritdoc}
     */
    public function onInit()
    {
        parent::onInit();
        add_action($this->getSendHook(), array($this, 'send'));
    }

    /**
     * Saves the action.
     * @param string $action The name of the action.
     */
    public function save($action)
    {
        $this->getModel()->save($action);
    }

    /**
     * Sends the stats.
     */
    public function send()
    {
        $environment = $this->getEnvironment();

        /** @var GirdGallery_Settings_Module $settings */
        $settings    = $environment->getModule('settings');
        $registry    = $settings->getRegistry();

        $stats   = $this->getModel();
        $request = $this->getRequest();

        if (!$stats->isReadyToSend() || $registry->get('send_stats') !== 1) {
            return;
        }

        if ($environment->isDev()
            || 'localhost' === $request->server->get('server_name')
        ) {

            // Allow to send stats in development mode with debugSendStats param.
            if (!$request->query->has('debugSendStats')) {
                $stats->clear();
                return;
            }
        }

        if ($stats->send()) {
            $stats->clear();
        }
    }

    /**
     * Lazy loading for the stats model.
     * @return SupsysticSlider_Stats_Model
     */
    protected function getModel()
    {
        if (!$this->stats) {
            $environment = $this->getEnvironment();

            $this->stats = new SupsysticSlider_Stats_Model(
                $environment->isDev(),
                $environment
            );

            if (null !== $logger = $environment->getLogger()) {
                $this->stats->setLogger($logger);
            }
        }

        return $this->stats;
    }

    /**
     * Returns the hook, after which plugin will try to send stats.
     * @return string
     */
    protected function getSendHook()
    {
        $environment = $this->getEnvironment();

        $prefix = $environment->getConfig()->get('hooks_prefix');

        return $prefix . 'after_modules_loaded';
    }
}
