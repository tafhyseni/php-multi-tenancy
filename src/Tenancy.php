<?php

namespace Tafhyseni\PhpMultiTenancy;

use Exception;

class Tenancy
{
    const STRING_SHUFFLE = 5;

    protected $config = [
        'CREATE' => 'CREATE TABLE {table} LIKE {database_name}.{table}',
        'INSERT' => 'INSERT INTO {tenancy_name}.{table} SELECT * FROM {database_name}.{table}',
    ];

    /**
     * Whether transactions will be used or not.
     */
    public $transactions = true;

    /**
     * Will be holding the DB Connection.
     */
    public $connection;

    /**
     * Holds the main database name.
     */
    public $database;
    public $database_name;

    /**
     * Will be holding the Tenancy Connection.
     */
    public $tenancy_connection;
    public $tenancy_name;

    /**
     * Holds the tenancy database name.
     */
    public $tenant;

    /**
     * Raw Query.
     */
    public $query;

    public function __construct($db_config = [], $dummy = false)
    {
        if ($dummy) {
            return true;
        }

        $this->_connect_main($db_config);

        $this->_connect_tenant($db_config);
    }

    private function _connect_main($db_config)
    {
        $this->connection = mysqli_connect($db_config['hostname'], $db_config['username'], $db_config['password']);
        if (! $this->connection) {
            $this->_throwError($this->connection);
        }

        if (isset($db_config['database'])) {
            mysqli_close($this->connection);
            $this->database = mysqli_connect($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);
            $this->database_name = $db_config['database'];
        }
    }

    /**
     * Handles the selection of Tenancy Database.
     */
    private function _connect_tenant($db_config)
    {
        if (isset($db_config['tenancy_hostname']) && isset($db_config['tenancy_username'])) {
            $this->tenancy_connection = mysqli_connect($db_config['tenancy_hostname'], $db_config['tenancy_username'], $db_config['tenancy_password']);

            if (! $this->tenancy_connection) {
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
     * Close connections.
     */
    private function _close_connections()
    {
        mysqli_close($this->database);
        mysqli_close($this->tenancy_connection);
    }

    /**
     * Generates a full tenancy.
     * @param [name] string
     * @return string
     */
    public function generate(string $name = null, $tables = [], $withData = false)
    {
        if (! $name) {
            $name = $this->_shuffle_name();
        }

        $query = 'CREATE DATABASE '.$name;

        // Create schema
        $run = mysqli_query($this->tenancy_connection, $query);

        // connect tenant
        $this->tenancy_connection->select_db($name);
        $this->tenancy_name = $name;

        // Copy a structure
        if (empty($tables)) {
            $tables = $this->get_tables();
        }

        foreach ($tables as $table) {
            $this->copy_table_from_master($table, $withData);
        }

        $this->_close_connections();

        return $name;
    }

    /**
     * Returns all tables name inside main database.
     */
    public function get_tables()
    {
        return array_column(mysqli_fetch_all($this->database->query('SHOW TABLES')), 0);
    }

    /**
     * Copies a table from Master Database.
     * @param [withData] bool
     * @return bool
     */
    public function copy_table_from_master($table, $withData = false)
    {
        $res = mysqli_query($this->tenancy_connection, $this->_prepare_query('CREATE', ['table' => $table, 'database_name' => $this->database_name]));

        if (! $res) {
            $this->_throwError($this->tenancy_connection);
        }

        if ($withData) {
            $res = mysqli_query($this->tenancy_connection, $this->_prepare_query('INSERT', ['tenancy_name' => $this->tenancy_name, 'table' => $table, 'database_name' => $this->database_name]));
            if (! $res) {
                $this->_throwError($this->tenancy_connection);
            }
        }
    }

    /**
     * Auto generate Tenant name.
     * @return string
     */
    public function auto_name()
    {
        return $this->_shuffle_name();
    }

    /**
     * Error handling.
     */
    private function _throwError($connection)
    {
        throw new Exception('An error happened '.mysqli_error($connection));
    }

    /**
     * If not already set, this will auto-generate tenancy name.
     * @return string
     */
    private function _shuffle_name()
    {
        $shuffle = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(self::STRING_SHUFFLE / strlen($x)))), 1, self::STRING_SHUFFLE);

        return $this->database_name.'_'.$shuffle.'_'.time();
    }

    /**
     * Prepares query of our pre defined configuration queries.
     * @return string
     */
    private function _prepare_query($type, $parameters = [])
    {
        if ($type === 'CREATE') {
            if ($parameters['table'] == '' || $parameters['database_name'] == '') {
                throw new Exception('An error happened: Missing parameters on database creation! ');
            }

            return str_replace(
                ['{table}', '{database_name}'],
                ['`'.$parameters['table'].'`', $parameters['database_name']],
                $this->config['CREATE']
            );
        } elseif ($type === 'INSERT') {
            if ($parameters['tenancy_name'] == '' || $parameters['table'] == '' || $parameters['database_name'] == '') {
                throw new Exception('An error happened: Missing parameters on database inserts! ');
            }

            return str_replace(
                ['{tenancy_name}', '{table}', '{database_name}'],
                [$parameters['tenancy_name'], '`'.$parameters['table'].'`', $parameters['database_name']],
                $this->config['INSERT']
            );
        }
    }
}
