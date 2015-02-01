<?php

/**
 * Interface SupsysticSlider_Settings_SettingsStorageInterface
 *
 * @package SupsysticSlider\Settings
 * @author Artur Kovalevsky
 */
interface SupsysticSlider_Settings_SettingsStorageInterface
{

    /**
     * Adds new option to the storage
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function add($key, $value);

    /**
     * Returns the value of the option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Updates the value by the key
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value);

    /**
     * Deletes option by the key
     *
     * @param string $key
     * @return void
     */
    public function delete($key);

    /**
     * Returns all options
     *
     * @param string $prefix
     * @return array
     */
    public function all($prefix);

} 