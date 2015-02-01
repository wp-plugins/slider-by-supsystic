<?php


class Rsc_Promo_Module extends Rsc_Mvc_Module
{

    /**
     * {@inheritdoc}
     */
    public function onInit()
    {
        parent::onInit();

        $code = $this->getEnvironment()->getPluginName();

        if ($this->getRequest()->query->has('reset_promo')) {
            update_option(sprintf('%s_promo_shown', $code), 0);
        }

        if (0 === (int) get_option(sprintf('%s_promo_shown', $code))) {
            $this->getEnvironment()
                ->getConfig()
                ->set('default_module', 'promo');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onInstall()
    {
        parent::onInstall();

        $code = $this->getEnvironment()->getPluginName();

        //Change value to enable promo on install
        add_option(sprintf('%s_promo_shown', $code), 1);
    }

}
