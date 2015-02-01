<?php

/**
 * Interface Rsc_Form_Filter_Interface
 * Interface of the form filter
 *
 * @package Rsc\Form\Filter
 * @author Artur Kovalevsky
 * @copyright Copyright (c) 2015, supsystic.com
 * @link supsystic.com
 */
interface Rsc_Form_Filter_Interface
{
    /**
     * Filters data
     * @param mixed $data The data that filter will be applied
     */
    public function apply($data);
} 