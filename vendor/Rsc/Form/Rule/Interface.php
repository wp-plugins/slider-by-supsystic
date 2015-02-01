<?php

/**
 * Interface Rsc_Form_Rule_Interface
 * Interface for validation rules
 *
 * @package Rsc\Form\Rule
 * @author Artur Kovalevsky
 * @copyright Copyright (c) 2015, supsystic.com
 * @link supsystic.com
 */
interface Rsc_Form_Rule_Interface
{
    /**
     * Constructor
     * @param string $label Field label
     * @param mixed $parameters Rule parameters
     */
    public function __construct($label, $parameters = null);

    /**
     * Validate specified field
     * @param mixed $field Field data
     * @return bool TRUE if field is valid, FALSE otherwise
     */
    public function validate($field);

    /**
     * Returns rule error message
     * @return string Error message
     */
    public function getMessage();
} 