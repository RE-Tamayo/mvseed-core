<?php

namespace Mvseed\Application;

use Mvseed\Database\Table;
use Mvseed\Database\Database;
use Exception;

/**
 * The BaseModel class is a base class for all database models.
 *
 * It provides a simple interface for creating and accessing database tables.
 */
class BaseModel
{
    private $connection;
    private $tables = [];

    /**
     * Constructs a new BaseModel instance.
     *
     * Establishes a new database connection.
     */
    public function __construct()
    {
        $this->connection = Database::connect();
    }

    /**
     * Retrieves an instance of the specified table.
     *
     * @param string $table The name of the table to retrieve.
     *
     * @return Table An instance of the specified table.
     */
    protected function useTable($table)
    {
        if(in_array($table, array_keys($this->tables))) {
            return $this->tables[$table];
        }
        else {
            throw new Exception('Caught exception: Unknown table specified, make sure that the table specified is added by using addTable() function, and make sure that the spelling of the table name is correct.');
        }
    }

    /**
     * Adds a new table to the list of tables associated with this BaseModel instance.
     *
     * @param string $table_name The name of the table to add.
     * @param string $primary_key The name of the primary key column for the table.
     * @param array $columns An associative array of column names and types for the table.
     */
    protected function addTable($table_name, $primary_key, $columns)
    {
        $table = new Table($this->connection, $table_name, $primary_key, $columns);
        $this->tables[$table_name] = $table;
    }
}
