<?php

/**
 * GUID column.
 * 
 * @package    Kohana/ORM
 * @author     Koseven Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class ORM_Behavior_Guid extends ORM_Behavior {
    
    /**
     * Table column for GUID value.
     * @var string
     */
    protected $_guid_column = 'guid';

    /**
     * Allow model creaton on guid key only.
     * @var boolean
     */
    protected $_guid_only = TRUE;

    /**
     * Create GUID.
     * @return string
     */
    public function generate_guid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), 
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), 
            mt_rand(0, 0xffff), 
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Check GUID.
     * @param string $id
     * @return bool
     */
    public function valid_guid($id)
    {
        if ($id AND is_string($id))
        {
            return (bool) preg_match('/^[\d\w]{8}-[\d\w]{4}-[\d\w]{4}-[\d\w]{4}-[\d\w]{12}$/m', $id);
        }
        
        return FALSE;
    }
    
    /**
     * Set GUID.
     * @param ORM $model
     * @return void
     */
    private function create_guid(ORM $model)
    {
        $current_guid = $model->get($this->_guid_column);

        // Try to create a new GUID.
        $query = DB::select()->from($model->table_name())
                             ->where($this->_guid_column, '=', ':guid')
                             ->limit(1);
        
        while (empty($current_guid))
        {
            $current_guid = $this->generate_guid();
            $query->param(':guid', $current_guid);
            if ($query->execute()->get($model->primary_key(), FALSE) !== FALSE)
            {
                if (Kohana::$errors)
                {
                    Log::instance()->add(
                        Log::NOTICE,
                        'Duplicate GUID created for table :name.',
                        [':name' => $model->table_name()]
                    );
                }
                $current_guid = '';
            }
        }
        
        $model->set($this->_guid_column, $current_guid);
    }

    /**
     * Constructs a behavior object.
     *
     * @param array $config Configuration parameters.
     */
    protected function __construct($config)
    {
        parent::__construct($config);

        $this->_guid_column = $config['column'] ?? $this->_guid_column;
        $this->_guid_only = $config['guid_only'] ?? $this->_guid_only;
    }
    
    /**
     * Constructs a new model and loads a record if given.
     *
     * @param   ORM   $model The model.
     * @param   mixed $id    Parameter for find or object to load.
     */
    public function on_construct($model, $id)
    {
        if ($this->valid_guid($id))
        {
            $model->where($this->_guid_column, '=', $id)->find();
            // Prevent further record loading
            return FALSE;
        }

        return TRUE;
    }

    /**
    * The model is updated, add a guid value if empty.
    *
    * @param   ORM   $model The model.
    */
    public function on_update($model)
    {
        $this->create_guid($model);
    }

    /**
    * A new model is created, add a guid value.
    *
    * @param   ORM   $model The model.
    */
    public function on_create($model)
    {
        $this->create_guid($model);
    }
}
