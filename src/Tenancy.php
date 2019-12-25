<?php

namespace Tafhyseni\PhpMultiTenancy;

use Exception;

class Tenancy 
{
    /**
     * Will be holding the DB Connection
     */
    public $connection;

    /**
     * Holds the main database name
     */
    public $database;

    /**
     * Raw Query
     */
    public $query;

    public function __construct($query, $db_config = array())
    {
        $this->connection = mysql_connect($db_config['hostname'], $db_config['username'], $db_config['password']);
        if (!$this->connection) {
            $this->_throwError();
        }

        $this->database = mysql_select_db($db_config['database'], $this->connection);
        if (!$this->database) {
            $this->_throwError();
        }
        
        $this->query = $query;
    }

    /**
     * Error handling
     */
    private function _throwError()
    {
        throw new Exception("An error happened " . mysql_error());
    }

    public function index()
    {
        $result = mysql_query($this->query);
        if (!$result) {
            die('Invalid query: ' . mysql_error());
        }
    }
}