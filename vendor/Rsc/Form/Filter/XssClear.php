<?php

/**
 * Class Rsc_Form_Filter_XssClear
 * These filter cleans the data from possible XSS-attacks
 *
 * @package Rsc\Form\Filter
 * @author Artur Kovalevsky
 * @copyright Copyright (c) 2015, supsystic.com
 * @link supsystic.com
 */
class Rsc_Form_Filter_XssClear implements Rsc_Form_Filter_Interface
{
    /**
     * Filters data
     * @param mixed $data The data that filter will be applied
     * @return string
     */
    public function apply($data)
    {
        return htmlspecialchars($data, ENT_QUOTES, get_bloginfo('charset'));
    }
}