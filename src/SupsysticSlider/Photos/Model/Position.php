<?php

class SupsysticSlider_Photos_Model_Position extends SupsysticSlider_Core_BaseModel
{

    const SCOPE_FOLDER  = 'folder';
    const SCOPE_GALLERY = 'gallery';
    const SCOPE_MAIN    = 'main';

    /**
     * @var string
     */
    protected $table;

    /**
     * Constructor.
     */
    public function __construct($debugEnabled = false)
    {
        parent::__construct();

        $this->debugEnabled = (bool) $debugEnabled;
        $this->table        = $this->db->prefix . 'rs_photos_pos';
    }

    /**
     * Updates the position
     * @param  array $data
     * @return bool
     */
    public function update(array $data)
    {
        if (!isset($data['elements']) || count($data['elements']) < 1) {
            return false;
        }

        $this->clearScope($data['scope'], $data['scope_id']);

        foreach ($data['elements'] as $element) {
            $this->updatePosition(array(
                'photo_id' => (int) $element['photo_id'],
                'position' => (int) $element['position'],
                'scope_id' => (int) $data['scope_id'],
                'scope'    => $data['scope'],
            ));
        }

        return true;
    }

    /**
     * Updates position for the single row.
     * @param  array $row
     * @return bool
     */
    public function updatePosition(array $row)
    {
        $query = $this->getQueryBuilder()
            ->insertInto($this->table)
            ->fields(array_keys($row))
            ->values(array_values($row));

        if (!$this->db->query($query->build())) {
            return false;
        }

        return true;
    }

    /**
     * Clears the positions in the selected scope.
     * @param string $scope Scope type.
     * @param int    $id    Scope ID.
     */
    public function clearScope($scope = self::SCOPE_MAIN, $id = 0)
    {
        $query = $this->getQueryBuilder()
            ->deleteFrom($this->table)
            ->where('scope', '=', $scope)
            ->andWhere('scope_id', '=', (int) $id);

        $this->db->query($query->build());
    }

    /**
     * Returns the positions of the element in the specific scope.
     * @param  int    $id      Element Id.
     * @param  string $scope   Scope type.
     * @param  int    $scopeId Scope Id.
     * @return int
     */
    public function getPosition($id, $scope = self::SCOPE_MAIN, $scopeId = 0)
    {
        $query = $this->getQueryBuilder()
            ->select('position')
            ->from($this->table)
            ->where('scope', '=', $scope)
            ->andWhere('scope_id', '=', (int) $scopeId)
            ->andWhere('photo_id', '=', (int) $id);

        if (null === $row = $this->db->get_row($query->build())) {
            return 0;
        }

        return $row->position;
    }

    /**
     * Extends the photo object with the 'position' property.
     * @param  array|object $photo Photo object.
     * @param  string       $scope   Scope type.
     * @param  int          $scopeId Scope Id.
     * @return array|object
     */
    public function setPosition($photo, $scope = self::SCOPE_MAIN, $scopeId = 0)
    {
        $isArray = false;

        if (is_array($photo)) {
            $photo = (object) $photo;
            $isArray = true;
        }

        $photo->position = $this->getPosition($photo->id, $scope, $scopeId);

        return $isArray ? (array) $photo : $photo;
    }

    /**
     * Sorts an array of the photos by thier position.
     * @param  array $photos An array of the photos.
     * @return array
     */
    public function sort(array $photos)
    {
        $isObjectCollection = false;
        $position = array();
        $sorted = array();

        if (empty($photos)) {
            return array();
        }

        // If it is collection of the StdClass, them we are convert it to array.
        if (is_object($photos[0])) {
            $isObjectCollection = true;
            $photos = array_map(array($this, 'toArray'), $photos);
        }

        // Create row list
        foreach ($photos as $key => $row) {
            $position[$key] = $row['position'];
        }

        // Sort array by position value and ascend order.
        array_multisort($position, SORT_ASC, $photos);

        // If $photos is collection of the objects, then conver rows to the objs.
        if ($isObjectCollection) {
            return array_map(array($this, 'toObject'), $photos);
        }

        // ... or simply return array
        return $photos;
    }

    /**
     * Casts the element to array.
     * @param  object $element
     * @return array
     */
    public function toArray($element)
    {
        return (array) $element;
    }

    /**
     * Casts the element to object
     * @param  array $element
     * @return object
     */
    public function toObject($element)
    {
        return (object) $element;
    }
}
