<?php

/**
 * Slider resources (images, folders).
 */
class SupsysticSlider_Slider_Model_Resources extends SupsysticSlider_Core_BaseModel
{

    const MODE_ROW = 0;
    const MODE_COLLECTION = 1;

    const TYPE_IMAGE  = 'image';
    const TYPE_FOLDER = 'folder';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable($this->db->prefix . 'rs_resources');
    }

    /**
     * Returns resource by id.
     *
     * @param int $id Resource Identifier.
     * @return mixed|null
     */
    public function getById($id)
    {
        return $this->getBy('id', (int) $id, self::MODE_ROW);
    }

    /**
     * Returns all resources with the specific id.
     * It may be folder and photos with one id.
     *
     * @param int $resourceId Identifier of the resource.
     * @return mixed|null
     */
    public function getByResourceId($resourceId)
    {
        return $this->getBy(
            'resource_id',
            (int)$resourceId,
            self::MODE_COLLECTION
        );
    }

    /**
     * Returns all resources with the specific type.
     *
     * @param string $resourceType Type of the resource.
     * @return mixed|null
     */
    public function getByResourceType($resourceType)
    {
        return $this->getBy(
            'resource_type',
            (string)$resourceType,
            self::MODE_COLLECTION
        );
    }

    /**
     * Returns all resources that attached to the specific slider.
     *
     * @param int $sliderId Identifier of the slider.
     * @return mixed|null
     */
    public function getBySliderId($sliderId)
    {
        return $this->getBy('slider_id', (int)$sliderId, self::MODE_COLLECTION);
    }

    /**
     * Returns all resource for specified slider object.
     *
     * @param object $slider
     * @return object
     * @throws InvalidArgumentException
     */
    public function getBySlider($slider)
    {
        if (!is_object($slider)) {
            throw new InvalidArgumentException(sprintf(
                'Parameter 1 must be a object, %s given.',
                gettype($slider)
            ));
        }

        if (!property_exists($slider, 'id') || !$slider->id) {
            throw new InvalidArgumentException(sprintf(
                'Property "%s" must be defined and valid.',
                'id'
            ));
        }

        $slider->resources = $this->getBySliderId($slider->id);

        return $slider;
    }

    /**
     * Selects data by specified field.
     *
     * @param string $field Field title.
     * @param mixed $value Value to search.
     * @param int $mode Return mode.
     * @return mixed|null
     */
    public function getBy($field, $value, $mode = self::MODE_ROW)
    {
        $query = $this->getQueryBuilder()
            ->select('*')
            ->from($this->getTable())
            ->where($field, '=', $value);

        return $this->getResult($query->build(), $mode);
    }

    /**
     * Adds new resource to the slider.
     *
     * @param int $sliderId Slider identifier.
     * @param string $resourceType Resource type.
     * @param int $resourceId Resource identifier.
     * @return bool
     */
    public function add($sliderId, $resourceType, $resourceId)
    {
        $query = $this->getQueryBuilder()
            ->insertInto($this->getTable())
            ->fields('slider_id', 'resource_type', 'resource_id')
            ->values((int)$sliderId, (string)$resourceType, (int)$resourceId);

        if (!$this->db->query($query->build())) {
            $this->setLastError($this->db->last_error);

            return false;
        }

        $this->setInsertId($this->db->insert_id);

        return true;
    }

    /**
     * Adds an image to the slider.
     *
     * @param int $sliderId Slider identifier.
     * @param int $imageId Image identifier.
     * @return bool
     */
    public function addImage($sliderId, $imageId)
    {
        return $this->add($sliderId, self::TYPE_IMAGE, $imageId);
    }

    /**
     * Adds folder to the slider.
     *
     * @param int $sliderId Slider identifier.
     * @param int $folderId Folder identifier.
     * @return bool
     */
    public function addFolder($sliderId, $folderId)
    {
        return $this->add($sliderId, self::TYPE_FOLDER, $folderId);
    }

    /**
     * Adds resources from assoc array.
     *
     * @param int $sliderId Slider identifier.
     * @param array $items An array of the items.
     */
    public function addArray($sliderId, array $items)
    {
        foreach ($items as $type => $identifiers) {
            if (count($identifiers) > 0) {
                foreach ($identifiers as $id) {
                    if ($type === self::TYPE_FOLDER) {
                        $this->addFolder($sliderId, $id);
                    } else {
                        $this->addImage($sliderId, $id);
                    }
                }
            }
        }
    }

    /**
     * Removes photo by identifier from the resources.
     *
     * @param int $photoId Photo identifier.
     * @return bool
     */
    public function deletePhotoById($photoId)
    {
        $query = $this->getQueryBuilder()
            ->deleteFrom($this->getTable())
            ->where('resource_type', '=', 'photo')
            ->andWhere('resource_id', '=', (int)$photoId);

        if (false === $this->db->query($query->build())) {
            $this->setLastError($this->db->last_error);

            return false;
        }

        return true;
    }

    public function delete($sliderId, $resourceId, $resourceType)
    {
        $query = $this->getQueryBuilder()
            ->deleteFrom($this->getTable())
            ->where('resource_id', '=', (int)$resourceId)
            ->andWhere('resource_type', '=', $resourceType)
            ->andWhere('slider_id', '=', (int)$sliderId);

        if (!$this->db->query($query->build())) {
            $this->setLastError($this->db->last_error);

            return false;
        }

        return true;
    }

    /**
     * Returns the query results based on the specified mode.
     *
     * @param string $query
     * @param int $mode
     * @return mixed|null
     */
    protected function getResult($query, $mode)
    {
        switch ($mode) {
            case self::MODE_ROW:
                return $this->db->get_row($query);
                break;
            case self::MODE_COLLECTION:
                return $this->db->get_results($query);
                break;
            default:
                return null;
        }
    }

}
