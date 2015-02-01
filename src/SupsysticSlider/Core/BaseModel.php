<?php


class SupsysticSlider_Core_BaseModel extends Rsc_Mvc_Model implements Rsc_Logger_AwareInterface
{

    /**
     * @var bool
     */
    protected $debugEnabled;

    /**
     * @var string
     */
    protected $lastError;

    /**
     * @var int
     */
    protected $insertId;

    /**
     * @var Rsc_Logger_Interface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var Rsc_Environment
     */
    protected $environment;

    /**
     * Sets the debug mode enabled
     *
     * @param bool $debugEnabled
     * @return SupsysticSlider_Core_BaseModel
     */
    public function setDebugEnabled($debugEnabled)
    {
        $this->debugEnabled = $debugEnabled;
        return $this;
    }

    /**
     * Returns the last insert id
     *
     * @return int
     */
    public function getInsertId()
    {
        return $this->insertId;
    }

    /**
     * Returns the last MySQL error
     *
     * @return string|null
     */
    public function getLastError()
    {
        if (!$this->lastError) {
            $this->lastError = $this->db->last_error;
        }

        return $this->lastError;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param Rsc_Logger_Interface $logger
     * @return null
     */
    public function setLogger(Rsc_Logger_Interface $logger)
    {
        $this->logger = $logger;
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $lastError
     */
    public function setLastError($lastError)
    {
        $this->lastError = $lastError;
    }

    /**
     * @param int $insertId
     */
    public function setInsertId($insertId)
    {
        $this->insertId = $insertId;
    }

    /**
     * @return boolean
     */
    public function getDebugEnabled()
    {
        return $this->debugEnabled;
    }
}
