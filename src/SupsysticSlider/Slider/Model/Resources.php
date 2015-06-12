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

    static public function getFontsList() {
        return array("Abel", "Abril Fatface", "Aclonica", "Acme", "Actor", "Adamina", "Advent Pro",
            "Aguafina Script", "Aladin", "Aldrich", "Alegreya", "Alegreya SC", "Alex Brush", "Alfa Slab One", "Alice",
            "Alike", "Alike Angular", "Allan", "Allerta", "Allerta Stencil", "Allura", "Almendra", "Almendra SC", "Amaranth",
            "Amatic SC", "Amethysta", "Andada", "Andika", "Angkor", "Annie Use Your Telescope", "Anonymous Pro", "Antic",
            "Antic Didone", "Antic Slab", "Anton", "Arapey", "Arbutus", "Architects Daughter", "Arimo", "Arizonia", "Armata",
            "Artifika", "Arvo", "Asap", "Asset", "Astloch", "Asul", "Atomic Age", "Aubrey", "Audiowide", "Average",
            "Averia Gruesa Libre", "Averia Libre", "Averia Sans Libre", "Averia Serif Libre", "Bad Script", "Balthazar",
            "Bangers", "Basic", "Battambang", "Baumans", "Bayon", "Belgrano", "Belleza", "Bentham", "Berkshire Swash",
            "Bevan", "Bigshot One", "Bilbo", "Bilbo Swash Caps", "Bitter", "Black Ops One", "Bokor", "Bonbon", "Boogaloo",
            "Bowlby One", "Bowlby One SC", "Brawler", "Bree Serif", "Bubblegum Sans", "Buda", "Buenard", "Butcherman",
            "Butterfly Kids", "Cabin", "Cabin Condensed", "Cabin Sketch", "Caesar Dressing", "Cagliostro", "Calligraffitti",
            "Cambo", "Candal", "Cantarell", "Cantata One", "Cardo", "Carme", "Carter One", "Caudex", "Cedarville Cursive",
            "Ceviche One", "Changa One", "Chango", "Chau Philomene One", "Chelsea Market", "Chenla", "Cherry Cream Soda",
            "Chewy", "Chicle", "Chivo", "Coda", "Coda Caption", "Codystar", "Comfortaa", "Coming Soon", "Concert One",
            "Condiment", "Content", "Contrail One", "Convergence", "Cookie", "Copse", "Corben", "Cousine", "Coustard",
            "Covered By Your Grace", "Crafty Girls", "Creepster", "Crete Round", "Crimson Text", "Crushed", "Cuprum", "Cutive",
            "Damion", "Dancing Script", "Dangrek", "Dawning of a New Day", "Days One", "Delius", "Delius Swash Caps",
            "Delius Unicase", "Della Respira", "Devonshire", "Didact Gothic", "Diplomata", "Diplomata SC", "Doppio One",
            "Dorsa", "Dosis", "Dr Sugiyama", "Droid Sans", "Droid Sans Mono", "Droid Serif", "Duru Sans", "Dynalight",
            "EB Garamond", "Eater", "Economica", "Electrolize", "Emblema One", "Emilys Candy", "Engagement", "Enriqueta",
            "Erica One", "Esteban", "Euphoria Script", "Ewert", "Exo", "Expletus Sans", "Fanwood Text", "Fascinate", "Fascinate Inline",
            "Federant", "Federo", "Felipa", "Fjord One", "Flamenco", "Flavors", "Fondamento", "Fontdiner Swanky", "Forum",
            "Francois One", "Fredericka the Great", "Fredoka One", "Freehand", "Fresca", "Frijole", "Fugaz One", "GFS Didot",
            "GFS Neohellenic", "Galdeano", "Gentium Basic", "Gentium Book Basic", "Geo", "Geostar", "Geostar Fill", "Germania One",
            "Give You Glory", "Glass Antiqua", "Glegoo", "Gloria Hallelujah", "Goblin One", "Gochi Hand", "Gorditas",
            "Goudy Bookletter 1911", "Graduate", "Gravitas One", "Great Vibes", "Gruppo", "Gudea", "Habibi", "Hammersmith One",
            "Handlee", "Hanuman", "Happy Monkey", "Henny Penny", "Herr Von Muellerhoff", "Holtwood One SC", "Homemade Apple",
            "Homenaje", "IM Fell DW Pica", "IM Fell DW Pica SC", "IM Fell Double Pica", "IM Fell Double Pica SC",
            "IM Fell English", "IM Fell English SC", "IM Fell French Canon", "IM Fell French Canon SC", "IM Fell Great Primer",
            "IM Fell Great Primer SC", "Iceberg", "Iceland", "Imprima", "Inconsolata", "Inder", "Indie Flower", "Inika",
            "Irish Grover", "Istok Web", "Italiana", "Italianno", "Jim Nightshade", "Jockey One", "Jolly Lodger", "Josefin Sans",
            "Josefin Slab", "Judson", "Julee", "Junge", "Jura", "Just Another Hand", "Just Me Again Down Here", "Kameron",
            "Karla", "Kaushan Script", "Kelly Slab", "Kenia", "Khmer", "Knewave", "Kotta One", "Koulen", "Kranky", "Kreon",
            "Kristi", "Krona One", "La Belle Aurore", "Lancelot", "Lato", "League Script", "Leckerli One", "Ledger", "Lekton",
            "Lemon", "Lilita One", "Limelight", "Linden Hill", "Lobster", "Lobster Two", "Londrina Outline", "Londrina Shadow",
            "Londrina Sketch", "Londrina Solid", "Lora", "Love Ya Like A Sister", "Loved by the King", "Lovers Quarrel",
            "Luckiest Guy", "Lusitana", "Lustria", "Macondo", "Macondo Swash Caps", "Magra", "Maiden Orange", "Mako", "Marck Script",
            "Marko One", "Marmelad", "Marvel", "Mate", "Mate SC", "Maven Pro", "Meddon", "MedievalSharp", "Medula One", "Merriweather",
            "Metal", "Metamorphous", "Michroma", "Miltonian", "Miltonian Tattoo", "Miniver", "Miss Fajardose", "Modern Antiqua",
            "Molengo", "Monofett", "Monoton", "Monsieur La Doulaise", "Montaga", "Montez", "Montserrat", "Moul", "Moulpali",
            "Mountains of Christmas", "Mr Bedfort", "Mr Dafoe", "Mr De Haviland", "Mrs Saint Delafield", "Mrs Sheppards",
            "Muli", "Mystery Quest", "Neucha", "Neuton", "News Cycle", "Niconne", "Nixie One", "Nobile", "Nokora", "Norican",
            "Nosifer", "Nothing You Could Do", "Noticia Text", "Nova Cut", "Nova Flat", "Nova Mono", "Nova Oval", "Nova Round",
            "Nova Script", "Nova Slim", "Nova Square", "Numans", "Nunito", "Odor Mean Chey", "Old Standard TT", "Oldenburg",
            "Oleo Script", "Open Sans", "Open Sans Condensed", "Orbitron", "Original Surfer", "Oswald", "Over the Rainbow",
            "Overlock", "Overlock SC", "Ovo", "Oxygen", "PT Mono", "PT Sans", "PT Sans Caption", "PT Sans Narrow", "PT Serif",
            "PT Serif Caption", "Pacifico", "Parisienne", "Passero One", "Passion One", "Patrick Hand", "Patua One", "Paytone One",
            "Permanent Marker", "Petrona", "Philosopher", "Piedra", "Pinyon Script", "Plaster", "Play", "Playball", "Playfair Display",
            "Podkova", "Poiret One", "Poller One", "Poly", "Pompiere", "Pontano Sans", "Port Lligat Sans", "Port Lligat Slab",
            "Prata", "Preahvihear", "Press Start 2P", "Princess Sofia", "Prociono", "Prosto One", "Puritan", "Quantico",
            "Quattrocento", "Quattrocento Sans", "Questrial", "Quicksand", "Qwigley", "Radley", "Raleway", "Rammetto One",
            "Rancho", "Rationale", "Redressed", "Reenie Beanie", "Revalia", "Ribeye", "Ribeye Marrow", "Righteous", "Rochester",
            "Rock Salt", "Rokkitt", "Ropa Sans", "Rosario", "Rosarivo", "Rouge Script", "Ruda", "Ruge Boogie", "Ruluko",
            "Ruslan Display", "Russo One", "Ruthie", "Sail", "Salsa", "Sancreek", "Sansita One", "Sarina", "Satisfy", "Schoolbell",
            "Seaweed Script", "Sevillana", "Shadows Into Light", "Shadows Into Light Two", "Shanti", "Share", "Shojumaru",
            "Short Stack", "Siemreap", "Sigmar One", "Signika", "Signika Negative", "Simonetta", "Sirin Stencil", "Six Caps",
            "Slackey", "Smokum", "Smythe", "Sniglet", "Snippet", "Sofia", "Sonsie One", "Sorts Mill Goudy", "Special Elite",
            "Spicy Rice", "Spinnaker", "Spirax", "Squada One", "Stardos Stencil", "Stint Ultra Condensed", "Stint Ultra Expanded",
            "Stoke", "Sue Ellen Francisco", "Sunshiney", "Supermercado One", "Suwannaphum", "Swanky and Moo Moo", "Syncopate",
            "Tangerine", "Taprom", "Telex", "Tenor Sans", "The Girl Next Door", "Tienne", "Tinos", "Titan One", "Trade Winds",
            "Trocchi", "Trochut", "Trykker", "Tulpen One", "Ubuntu", "Ubuntu Condensed", "Ubuntu Mono", "Ultra", "Uncial Antiqua",
            "UnifrakturCook", "UnifrakturMaguntia", "Unkempt", "Unlock", "Unna", "VT323", "Varela", "Varela Round", "Vast Shadow",
            "Vibur", "Vidaloka", "Viga", "Voces", "Volkhov", "Vollkorn", "Voltaire", "Waiting for the Sunrise", "Wallpoet",
            "Walter Turncoat", "Wellfleet", "Wire One", "Yanone Kaffeesatz", "Yellowtail", "Yeseva One", "Yesteryear", "Zeyada"
        );
    }

}
