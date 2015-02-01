<?php

/**
 * Class GirdGallery_Ajax_Module
 * GirdGallery AJAX processor
 *
 * @package GirdGallery\Ajax
 * @author Artur Kovalevsky
 */
class SupsysticSlider_Ajax_Module extends Rsc_Mvc_Module
{

    /**
     * {@inheritdoc}
     */
    public function onInit()
    {
        parent::onInit();

        add_action('wp_ajax_supsystic-slider', array($this, 'handle'));
    }

    /**
     * Handles the AJAX requests
     * @return void
     */
    public function handle()
    {
        $handler = new SupsysticSlider_Ajax_Handler($this->getEnvironment());
        $handler->handle();
    }

    /**
     * Returns the AJAX url
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return admin_url('admin-ajax.php');
    }
} 