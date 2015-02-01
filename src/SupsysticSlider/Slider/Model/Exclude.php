<?php


class SupsysticSlider_Slider_Model_Exclude extends SupsysticSlider_Core_BaseModel
{

    protected $availableEntities;

    public function __construct()
    {
        parent::__construct();

        $this->setTable($this->db->prefix . 'rs_exclude');
        $this->availableEntities = array('image', 'video');
    }

    public function has($sliderId, $entityId, $entityType)
    {
        $query = $this->getQueryBuilder()
            ->select('*')
            ->from($this->getTable())
            ->where('slider_id', '=', (int)$sliderId)
            ->andWhere('entity_id', '=', (int)$entityId)
            ->andWhere('entity_type', '=', htmlspecialchars($entityType));

        if (!$this->db->get_results($query->build())) {
            return false;
        }

        return true;
    }

    public function get($sliderId)
    {
        $query = $this->getQueryBuilder()
            ->select('*')
            ->from($this->getTable())
            ->where('slider_id', '=', $sliderId);

        return $this->db->get_results($query->build());
    }

    public function add($sliderId, $entityId, $entityType)
    {
        if (!$this->isValidEntityType($entityType)) {
            throw $this->invalidEntityTypeException($entityType);
        }

        $data = array(
            'slider_id'   => (int)$sliderId,
            'entity_id'   => (int)$entityId,
            'entity_type' => $entityType,
        );

        $query = $this->getQueryBuilder()
            ->insertInto($this->getTable())
            ->fields(array_keys($data))
            ->values(array_values($data));

        if (!$this->db->query($query->build())) {
            $this->setLastError($this->db->last_error);

            return false;
        }

        return true;
    }

    public function remove($sliderId, $entityId, $entityType)
    {
        if (!$this->isValidEntityType($entityType)) {
            throw $this->invalidEntityTypeException($entityType);
        }

        $query = $this->getQueryBuilder()
            ->deleteFrom($this->getTable())
            ->where('slider_id', '=', (int)$sliderId)
            ->andWhere('entity_id', '=', (int)$entityId)
            ->andWhere('entity_type', '=', $entityType);

        if (false === $this->db->query($query->build())) {
            $this->setLastError($this->db->last_error);

            return false;
        }

        return true;
    }

    public function removeExcluded($slider)
    {
        $excluded = $this->get($slider->id);

        if (!$excluded) {
            return $slider;
        }

        $exclude = array();
        foreach ($excluded as $element) {
            $exclude[$element->entity_type][] = $element->entity_id;
        }

        if (!property_exists($slider, 'entities') || !count($slider->entities)) {
            return $slider;
        }

        foreach ($slider->entities as $index => $entity) {
            if (isset($exclude[$entity->type]) && in_array($entity->id, $exclude[$entity->type])) {
                unset($slider->entities[$index]);
            }
        }

        return $slider;
    }

    protected function isValidEntityType($entityType)
    {
        return in_array($entityType, $this->availableEntities);
    }

    protected function invalidEntityTypeException($entityType)
    {
        return new InvalidArgumentException(sprintf(
            'Invalid entity type "%s".',
            htmlspecialchars($entityType)
        ));
    }
}
