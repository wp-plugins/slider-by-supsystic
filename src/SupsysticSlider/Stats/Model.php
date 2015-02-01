<?php

/**
 * Class SupsysticSlider_Stats_Model
 * @package SupsysticSlider\Stats
 */
class SupsysticSlider_Stats_Model extends SupsysticSlider_Core_BaseModel
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var Rsc_Environment
     */
    protected $environment;

    /**
     * @var int
     */
    protected $maxVisits;

    /**
     * Constructor
     * @param bool $debugEnabled
     */
    public function __construct($debugEnabled, Rsc_Environment $environment)
    {
        parent::__construct((bool)$debugEnabled);

        $this->table       = $this->db->prefix . 'gg_stats';
        $this->maxVisits   = 10;
        $this->environment = $environment;
    }

    /**
     * Saves the action.
     * @param string $action The name of the action.
     */
    public function save($action)
    {
        if (!$this->exists($action)) {
            $this->insert($action);
            return;
        }

        $this->update($action);
    }

    /**
     * Returns the all usage stats.
     * @return array
     */
    public function get()
    {
        $query = $this->getQueryBuilder();
        $query->select('*')->from($this->table);

        return $this->db->get_results($query->build(), ARRAY_A);
    }

    /**
     * Clears the stats.
     */
    public function clear()
    {
        $query = $this->getQueryBuilder();
        $query->deleteFrom($this->table);

        $this->db->query($query->build());
    }

    /**
     * Sends the usage stats.
     * @return bool
     */
    public function send()
    {
        $data = $this->get();

        $response = wp_remote_post(
            $this->getApiUrl(),
            array(
                'body' => array(
                    'site_url'    => get_bloginfo('wpurl'),
                    'site_name'   => get_bloginfo('name'),
                    'plugin_code' => $this->getCode(),
                    'all_stat'    => $data,
                ),
            )
        );

        if (is_wp_error($response)) {
            if ($this->logger) {
                $this->logger->error(
                    'Failed to send usage statistics: {error}',
                    array('error' => $response->get_error_message())
                );
            }

            return false;
        }

        return true;
    }

    /**
     * Inserts the new action to the database.
     * @param string $action The name of the action.
     */
    public function insert($action)
    {
        $query = $this->getQueryBuilder();

        $query->insertInto($this->table)
            ->fields('code', 'visits')
            ->values($action, 1);

        $this->db->query($query->build());
    }

    /**
     * Updates the specified action in the database.
     * @param string $action The name of the action.
     */
    public function update($action)
    {
        $query  = $this->getQueryBuilder();
        $visits = $this->getVisits($action);

        $query->update($this->table)
            ->where('code', '=', $action)
            ->fields('visits')
            ->values((int)$visits + 1);

        $this->db->query($query->build());
    }

    /**
     * Counts the action.
     * @param string $action The name of the action.
     * @return int
     */
    public function exists($action)
    {
        $query = $this->getQueryBuilder();

        $query->select('*')
            ->from($this->table)
            ->where('code', '=', $action);

        if (null === $data = $this->db->get_results($query->build())) {
            return false;
        }

        return true;
    }

    /**
     * Returns the action's "visits".
     * @param string $action The name of the action.
     * @return int
     */
    public function getVisits($action)
    {
        $query = $this->getQueryBuilder();

        $query->select('visits')
            ->from($this->table)
            ->where('code', '=', $action);

        if (null !== $data = $this->db->get_row($query->build())) {
            return $data->visits;
        }

        return 0;
    }

    /**
     * Sets max visits value.
     *
     * @param int $maxVisits
     */
    public function setMaxVisits($maxVisits)
    {
        $this->maxVisits = (int)$maxVisits;
    }

    /**
     * @return int
     */
    public function getMaxVisits()
    {
        return $this->maxVisits;
    }

    /**
     * Checks whether the one of the action has more then MAX_VISITS visits.
     * @return bool
     */
    public function isReadyToSend()
    {
        $query = $this->getQueryBuilder();

        $query->select('*')
            ->from($this->table);

        if (null === $data = $this->db->get_results($query->build())) {
            return false;
        }

        foreach ($data as $action) {
            if ($action->visits >= $this->maxVisits) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns plugin code.
     *
     * @return string|null
     */
    public function getCode()
    {
        if (!$this->environment) {
            return null;
        }

        return $this->environment->getPluginName();
    }

    /**
     * @return string
     */
    protected function getApiUrl()
    {
        if (!$this->apiUrl) {
            $this->apiUrl = 'aHR0cDovL3JlYWR5c2hvcHBpbmdjYXJ0LmNvbS8/bW9k' .
                            'PW9wdGlvbnMmYWN0aW9uPXNhdmVVc2FnZVN0YXQmcGw9cmNz';
        }

        return base64_decode($this->apiUrl);
    }
}
