<?php

class SupsysticSlider_Installer_Model extends SupsysticSlider_Core_BaseModel
{
    /**
     * Cheks whether the specified table exists.
     * @param  string $tableName Table name.
     * @return bool
     */
    public function tableExists($tableName)
    {
        $prefix = $this->db->prefix;
        $table  = str_replace('{prefix}', $prefix, $tableName);

        $query = 'SHOW TABLES LIKE \'' . $table . '\'';

        $result = $this->db->get_results($query);

        return (count($result) > 0 ? true : false);
    }

    /**
     * Replace {prefix} with the current database prefix.
     * @param  string $table Table name or query.
     * @return string
     */
    public function prefix($table)
    {
        return str_replace('{prefix}', $this->db->prefix, $table);
    }

    /**
     * Install plugin.
     * @param  array $queries An array of the queries.
     * @return bool
     */
    public function install($queries)
    {
        if (!is_array($queries)) {
            return false;
        }

        foreach ($queries as $table => $query) {
            $this->installTable($table, $query, false);
        }

        return true;
    }

    /**
     * Uninstall plugin.
     * @param  array $queries An array of the queries.
     * @return bool
     */
    public function drop($queries)
    {
        if (!is_array($queries)) {
            return false;
        }

        foreach ($queries as $table => $query) {
            $this->dropTable($table);
        }

        return true;
    }

    /**
     * Updates the database.
     * @param  array $queries An array of the queries.
     */
    public function update($queries)
    {
        if (!$queries || !is_array($queries)) {
            return;
        }

        foreach ($queries as $query) {
            $query = $this->prefix($query);
            $this->db->query($query);
        }
    }

    protected function installTable($table, $query, $drop = true)
    {
        $table = $this->prefix($table);

        if ($this->tableExists($table)) {
            if ($drop) {
                $this->dropTable($table);
                $this->delta($this->prefix($query));
            }

            return;
        }

		$this->delta($this->prefix($query));
    }

    protected function dropTable($table)
    {
        $query = 'DROP TABLE IF EXISTS ' . $this->prefix($table);
        $this->db->query($query);
    }
}
