<?php

namespace Tafhyseni\PhpMultiTenancy;

use Exception;

class Tenancy
{

    const STRING_SHUFFLE = 5;

    // protected $config;
    protected $config = [
        'CREATE' => 'CREATE TABLE {table} LIKE {database_name}.{table}',
        'INSERT' => 'INSERT INTO {tenancy_name}.{table} SELECT * FROM {database_name}.{table}'
    ];

    /**
     * Will be holding the DB Connection
     */
    public $connection;

    /**
     * Holds the main database name
     */
    public $database;
    public $database_name;

    /**
     * Will be holding the Tenancy Connection
     */
    public $tenancy_connection;
    public $tenancy_name;

    /**
     * Holds the tenancy database name
     */
    public $tenant;

    /**
     * Raw Query
     */
    public $query;

    public function __construct($query, $db_config = array())
    {
        $this->_connect_main($db_config);

        $this->_connect_tenant($db_config);
    }

    private function _connect_main($db_config)
    {
        $this->connection = mysqli_connect($db_config['hostname'], $db_config['username'], $db_config['password']);
        if (!$this->connection) {
            $this->_throwError($this->connection);
        }

        if (isset($db_config['database'])) {
            mysqli_close($this->connection);
            $this->database = mysqli_connect($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);
            $this->database_name = $db_config['database'];
        }
    }

    /**
     * Handles the selection of Tenancy Database
     */
    private function _connect_tenant($db_config)
    {
        if (isset($db_config['tenancy_hostname']) && isset($db_config['tenancy_username'])) {

            $this->tenancy_connection = mysqli_connect($db_config['tenancy_hostname'], $db_config['tenancy_username'], $db_config['tenancy_password']);

            if (!$this->tenancy_connection) {
                $this->_throwError($this->tenancy_connection);
            }

            if (isset($db_config['tenancy_database'])) {
                mysqli_close($this->tenancy_connection);
                $this->tenancy_connection = mysqli_connect($db_config['tenancy_hostname'], $db_config['tenancy_username'], $db_config['tenancy_password'], $db_config['tenancy_database']);
                $this->tenancy_name = $db_config['tenancy_database'];
            }
        }
    }

    /**
     * Close connections
     */
    private function _close_connections()
    {
        mysqli_close($this->database);
        mysqli_close($this->tenancy_name);
    }

    /**
     * Generates a full tenancy
     * @param [name] string
     */
    public function generate(string $name = null, $tables = array(), $withData = FALSE)
    {
        if (!$name) {
            $name = $this->_shuffle_name();
        }

        $query = "CREATE DATABASE " . $name;

        // Create schema
        $run = mysqli_query($this->tenancy_connection, $query);

        // connect tenant
        $this->tenancy_connection->select_db($name);
        $this->tenancy_name = $name;
        
        // Copy a structure
        if(empty($tables)) {
            $tables = $this->get_tables();
        }
        
        foreach($tables as $table) {
            $this->copy_table_from_master($table, $withData);
        }
    }

    /**
     * Returns all tables name inside main database
     */
    public function get_tables()
    {
        return array_column(mysqli_fetch_all($this->database->query('SHOW TABLES')),0);
    }

    /**
     * Copies a table from Master Database
     * @param [withData] bool
     * @return bool
     */
    public function copy_table_from_master($table, $withData = FALSE)
    {
        $res = mysqli_query($this->tenancy_connection, "CREATE TABLE " . $table . " LIKE " . $this->database_name . "." . $table);
        if(!$res) {
            $this->_throwError($this->tenancy_connection);
        }

        if($withData) {
            $res = mysqli_query($this->tenancy_connection, "INSERT INTO ". $this->tenancy_name . '.' . $table . " SELECT * FROM " . $this->database_name . "." . $table);
            if(!$res) {
                $this->_throwError($this->tenancy_connection);
            }
        }
    }

    /**
     * Error handling
     */
    private function _throwError($connection)
    {
        throw new Exception("An error happened " . mysqli_error($connection));
    }

    /**
     * If not already set, this will auto-generate tenancy name
     * @return string
     */
    private function _shuffle_name()
    {
        $shuffle = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(self::STRING_SHUFFLE / strlen($x)))), 1, self::STRING_SHUFFLE);
        return $this->database_name . '_' . $shuffle . '_' . time();
    }

    public function index()
    {
        print_r($this->config['CREATE']);
    }
}
