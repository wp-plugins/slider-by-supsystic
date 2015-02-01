<?php


class SupsysticSlider_Slider_Model_Sorting extends SupsysticSlider_Core_BaseModel
{
    public function __construct()
    {
        parent::__construct();

        $this->setTable($this->db->prefix . 'rs_sorting');
    }

    public function saveBySliderId($sliderId, $positions)
    {
        if (!$this->clearBySliderId($sliderId)) {
            return false;
        }

        foreach ($positions as $index => $position) {
            $fields = array('`slider_id`', '`index`', '`position`');
            $values = array_map('esc_sql', array($sliderId, $index, $position));

            $query = $this->getQueryBuilder()
                ->insertInto($this->getTable())
                ->fields($fields)
                ->values($values);

            if (false === $this->db->query($query->build())) {
                $this->setLastError($this->db->last_error);

                return false;
            }
        }

        return true;
    }

    public function getBySliderId($sliderId)
    {
        $query = $this->getQueryBuilder()
            ->select('*')
            ->from($this->getTable())
            ->where('slider_id', '=', (int)$sliderId);

        if (!$positions = $this->db->get_results($query->build())) {
            return null;
        }

        return $positions;
    }

    /**
     * @param int $sliderId
     * @return bool
     */
    public function clearBySliderId($sliderId)
    {
        $query = $this->getQueryBuilder()
            ->deleteFrom($this->getTable())
            ->where('slider_id', '=', (int)$sliderId);

        if (false === $this->db->query($query->build())) {
            return false;
        }

        return true;
    }

    public function sortById($sliderId, $entities)
    {
        $sorting = $this->getBySliderId($sliderId);

        if (!$sorting) {
            return $entities;
        }

        $positions = array();
        foreach ($sorting as $data) {
            $positions[$data->index] = (int)$data->position;
        }

        $position = array();

        foreach ($entities as $entity) {
            if (!isset($positions[$entity->index])) {
                $positions[$entity->index] = min($positions) - 1;
            }

            $position[$entity->index] = $positions[$entity->index];
        }

        array_multisort($position, SORT_ASC, $entities);

        return $entities;
    }

    public function sortBySlider($slider)
    {
        if (!is_object($slider) || !property_exists(
                $slider,
                'id'
            ) || !property_exists($slider, 'entities')
        ) {
            return $slider;
        }

        $slider->entities = $this->sortById($slider->id, $slider->entities);

        return $slider;
    }
} 