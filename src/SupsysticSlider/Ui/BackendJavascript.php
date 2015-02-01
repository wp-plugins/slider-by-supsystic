<?php

/**
 * Class SupsysticSlider_Ui_BackendJavascript
 */
class SupsysticSlider_Ui_BackendJavascript extends SupsysticSlider_Ui_Javascript
{
    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $request = Rsc_Http_Request::create();

        if (false !== strpos($request->query->get('page'), 'supsystic-slider')) {
            $this->register('admin_print_scripts');
        }
    }
}