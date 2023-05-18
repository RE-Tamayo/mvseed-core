<?php

declare(strict_types=1);

namespace Mvseed\Database;

use Mvseed\Database\AbstractionLayer;
use PDO;

/**
 * Class Table
 *
 * This class provides an interface to a database table.
 */
class Table extends AbstractionLayer
{
    /**
     * The PDO connection instance.
     *
     * @var PDO
     */
    protected PDO $connection;

    /**
     * The name of the table.
     *
     * @var string
     */
    protected string $table;

    /**
     * The name of the primary key column.
     *
     * @var string
     */
    protected string $primary_key;

    /**
     * An array of the column names in the table.
     *
     * @var array
     */
    protected array $columns;

    /**
     * Table constructor.
     *
     * @param PDO    $connection  The PDO connection instance.
     * @param string $table       The name of the table.
     * @param string $primary_key The name of the primary key column.
     * @param array  $columns     An array of the column names in the table.
     */
    public function __construct(PDO $connection, string $table, string $primary_key, array $columns)
    {
        $this->initialize($connection, $table, $primary_key, $columns);
    }

    /**
     * Initializes the instance properties.
     *
     * @param PDO    $connection  The PDO connection instance.
     * @param string $table       The name of the table.
     * @param string $primary_key The name of the primary key column.
     * @param array  $columns     An array of the column names in the table.
     *
     * @return void
     */
    public function initialize(PDO $connection, string $table, string $primary_key, array $columns): void
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->primary_key = $primary_key;
        $this->columns = $columns;
    }

    /**
     * Fetches all rows from the table.
     *
     * @param array $params An array of string to specify the columns to select for the WHERE clause of the SELECT statement.
     *
     * @return array An array of associative arrays containing the row data.
     */
    public function perform_fetch_all(array $params): array
    {
        return $this->fetch_all($params);
    }

    /**
     * Fetches a single row from the table.
     *
     * @param array  $params      An array of string to specify the columns to select.
     * @param string $where       The name of the column for the WHERE clause of the SELECT statement.
     * @param string $whereValue  The value of the column for the WHERE clause of the SELECT statement.
     *
     * @return array An associative array containing the row data.
     */
    public function perform_fetch(array $params, string $where, string $whereValue): array
    {
        return $this->fetch($params, $where, $whereValue);
    }

    /**
     * Creates a single row on the table.
     *
     * @param array  $params      An array of key-value pairs to specify the columns to select and their respective values.
     *
     * @return void
     */
    public function perform_create(array $params): void
    {
        $this->create($params);
    }

    /**
     * Updates a single row on the table.
     *
     * @param array  $params      An array of key-value pairs to specify the columns to select and their respective values.
     * @param string $where       The name of the column for the WHERE clause of the SELECT statement.
     * @param string $whereValue  The value of the column for the WHERE clause of the SELECT statement.
     *
     * @return void
     */
    public function perform_update(array $params, string $where, string $whereValue): void
    {
        $this->update($params, $where, $whereValue);
    }

    /**
     * Deletes a single row on the table.
     *
     * @param string $where       The name of the column for the WHERE clause of the SELECT statement.
     * @param string $whereValue  The value of the column for the WHERE clause of the SELECT statement.
     *
     * @return void
     */
    public function perform_delete(string $where, string $whereValue): void
    {
        $this->delete($where, $whereValue);
    }
}
