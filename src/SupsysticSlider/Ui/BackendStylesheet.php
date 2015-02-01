<?php

/**
 * Class SupsysticSlider_Ui_BackendStylesheet
 *
 * Loads the stylesheet to backend.
 */
class SupsysticSlider_Ui_BackendStylesheet extends SupsysticSlider_Ui_Stylesheet
{
    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $request = Rsc_Http_Request::create();

        if (false !== strpos($request->query->get('page'), 'supsystic-slider')) {
            $this->register('admin_print_styles');
        }
    }
} 