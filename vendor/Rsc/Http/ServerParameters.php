<?php

/**
 * Class Rsc_Http_ServerParameters
 * Basic request parameters extension for server globals
 *
 * @package Rsc\Http
 * @author Artur Kovalevsky
 * @copyright Copyright (c) 2015, supsystic.com
 * @link supsystic.com
 */
class Rsc_Http_ServerParameters extends Rsc_Http_Parameters
{

    /**
     * Get an associative array of HTTP headers from server variables
     * @return array
     */
    public function getHeaders()
    {
        $headers = array();

        foreach ($this->collection as $key => $value) {
            if (substr($key, 0, 4) === 'HTTP') {
                $headers[substr($key, 5)] = $value;
                $this->delete($key);
            }
        }

        return $headers;
    }

} 