<?php

declare(strict_types=1);

namespace Mvseed\Database;

use Exception;
use PDO;

/**
 * The AbstractionLayer class is an abstract class that is used as a base class for other classes that interact with a database using PDO.
 * 
 * It provides basic functionality to access a database, including
 * fetching data, deleting data, inserting data, and updating data.
 */
abstract class AbstractionLayer
{

    /**
     * The PDO connection instance.
     *
     * @var PDO
     */
    protected PDO $connection;

    /**
     * The name of the database table associated with the table.
     *
     * @var string
     */
    protected string $table;

    /**
     * The primary key for the table.
     *
     * @var string
     */
    protected string $primary_key;

    /**
     * The table's columns.
     *
     * @var array
     */
    protected array $columns;

    /**
     * Initialize the table.
     *
     * @return void
     */
    abstract protected function initialize(PDO $connection, string $table, string $primary_key, array $columns);

    /**
     * Fetch data from the database based on given parameters and where condition.
     *
     * @param array $params The columns to fetch from the table.
     * @param string $where The where condition.
     * @param string $whereValue The value of the where condition.
     *
     * @return array The fetched data.
     *
     * @throws Exception If $where or $whereValue is empty or missing or no column specified in parameter 1.
     */
    protected function fetch(array $params, string $where, string $whereValue): array
    {
        $whereValue = $this->sanitize_value($whereValue);
        $sql = '';
        $stmt = null;
        $data = null;
        if (count($params) < 1) {
            throw new Exception('Caught exception: Missing or empty arguments passed to parameter 1 of perform_fetch() function.');
        }
        if ($where == '') {
            throw new Exception('Caught exception: Missing or empty arguments passed to parameter 2 of perform_fetch() function.');
        } else if ($whereValue == '') {
            throw new Exception('Caught exception: Missing or empty arguments passed to parameter 3 of perform_fetch() function.');
        }
        if (count($params) > 1) {
            $params = implode(', ', $params);
            $sql = 'SELECT ' . $params . ' FROM ' . $this->table . ' WHERE ' . $where . ' = ' . $whereValue . ';';
        } else if (count($params) == 1) {
            $params = implode('', $params);
            $sql = 'SELECT ' . $params . ' FROM ' . $this->table . ' WHERE ' . $where . ' = ' . $whereValue . ';';
        } else {
            throw new Exception('Caught exception: Missing or empty arguments passed to parameter 1 of perform_fetch() function.');
        }
        $stmt = $this->connection->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage();
        }
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * Insert data into the database table.
     *
     * @param array $params The data to be inserted into the table.
     * 
     * @return void
     * 
     * @throws Exception If $params is empty or missing.
     */
    protected function create(array $params): void
    {
        $params = $this->sanitize_array_values($params);
        if (count($params) < 1) {
            throw new Exception('Caught exception: Missing or empty arguments passed to parameter 1 of perform_create() function.');
        }
        if (count($params) !== count($this->columns)) {
            throw new Exception('Caught exception: Number of input parameters does not match number of columns.');
        }
        $sql = '';
        $stmt = null;
        $string_columns = '';
        $string_params = '';
        $columns = array_keys($params);
        $params_values = array_values($params);
        foreach ($columns as $column) {
            $string_columns .= $column . ', ';
            $string_params .= '?, ';
        }
        // Remove the trailing comma and space from the generated strings
        $string_columns = rtrim($string_columns, ', ');
        $string_params = rtrim($string_params, ', ');
        $sql = 'INSERT INTO ' . $this->table . ' (' . $string_columns . ') VALUES (' . $string_params . ');';
        $stmt = $this->connection->prepare($sql);
        try {
            $stmt->execute($params_values);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage();
        }
    }


    /**
     * Updates records in the table with specified values where the given condition is met
     *
     * @param array $params An array of values to be updated in the columns of the table
     * @param string $where The name of the column to be used as the condition for the update statement
     * @param string $whereValue The value to match for the given column
     * 
     * @return void
     * 
     * @throws Exception if $where or $whereValue are empty strings
     */
    protected function update(array $params, string $where, string $whereValue): void
    {
        $whereValue = $this->sanitize_value($whereValue);
        $params = $this->sanitize_array_values($params);
        if (count($params) < 1) {
            throw new Exception('Caught exception: Missing or empty arguments passed to parameter 1 of perform_update() function.');
        }
        if (count($params) !== count($this->columns)) {
            throw new Exception('Caught exception: Number of input parameters does not match number of columns.');
        }
        $sql = '';
        $stmt = null;
        if ($where == '') throw new Exception('Caught exception: Missing or empty arguments passed parameter 2 of perform_update() function.');
        else if ($whereValue == '') throw new Exception('Caught exception: Missing or empty arguments passed parameter 3 of perform_update() function.');

        // Convert key-value pairs to column-value pairs
        $columns_values = [];
        foreach ($params as $key => $value) {
            $columns_values[] = $key . ' = ?';
        }
        $string_columns = implode(', ', $columns_values);

        $sql = 'UPDATE ' . $this->table . ' SET ' . $string_columns . ' WHERE ' . $where . ' = ?;';
        $stmt = $this->connection->prepare($sql);

        try {
            // Extract values from key-value pairs in the correct order
            $values = array_values($params);
            $new_params = array_merge($values, [$whereValue]);
            $stmt->execute($new_params);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage();
        }
    }


    /**
     * Deletes records from the table where the given condition is met
     *
     * @param string $where The name of the column to be used as the condition for the delete statement
     * @param string $whereValue The value to match for the given column
     * 
     * @return void
     * 
     * @throws Exception if $where or $whereValue are empty strings
     */
    protected function delete(string $where, string $whereValue): void
    {
        $whereValue = $this->sanitize_value($whereValue);
        $sql = '';
        $stmt = null;
        if ($where == '') throw new Exception('Caught exception: Missing or empty arguments passed parameter 1 of perform_delete() function.');
        else if ($whereValue == '') throw new Exception('Caught exception: Missing or empty arguments passed parameter 2 of perform_delete() function.');
        $sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $where . ' = ?;';
        $stmt = $this->connection->prepare($sql);
        try {
            $stmt->execute(array($whereValue));
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage();
        }
    }

    /**
     *Retrieves all data from the specified table with optional columns to fetch

     *@param array $params An array of columns to fetch (optional)

     *@return array An array of associative arrays containing the retrieved data

     *@throws Exception If no column is specified in the parameter array
     */
    protected function fetch_all(array $params = []): array
    {
        if (count($params) > 1) {
            $params = implode(', ', $params);
            $sql = 'SELECT ' . $params . ' FROM ' . $this->table . ';';
        } else if (count($params) == 1) {
            $params = implode('', $params);
            $sql = 'SELECT ' . $params . ' FROM ' . $this->table . ';';
        } else {
            throw new Exception('Caught exception: No column specified on parameter 1 of perform_fetch_all() function');
        }
        $stmt = $this->connection->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage();
        }
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * Sanitizes all values in the given array
     *
     * @param array $data An array to sanitize
     *
     * @return array An array with sanitized values
     */
    protected function sanitize_array_values(array $data): array
    {
        foreach ($data as &$value) {
            switch (gettype($value)) {
                case 'string':
                    $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // Sanitize HTML entities
                    break;
                case 'integer':
                    $value = (int) $value; // Convert to integer
                    break;
                case 'double':
                    $value = (float) $value; // Convert to float
                    break;
                case 'boolean':
                    $value = (bool) $value; // Convert to boolean
                    break;
                default:
                    $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // Sanitize HTML entities
                    break;
            }
        }
        unset($value); // Unset the reference to the last value to avoid unexpected behavior
        return $data;
    }

    /**
     * Sanitizes a value to prevent injection attacks and other vulnerabilities.
     *
     * @param mixed $value The value to sanitize.
     * @return mixed The sanitized value.
     */
    protected function sanitize_value($value)
    {
        switch (gettype($value)) {
            case 'string':
                $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // Sanitize HTML entities
                break;
            case 'integer':
                $value = (int) $value; // Convert to integer
                break;
            case 'double':
                $value = (float) $value; // Convert to float
                break;
            case 'boolean':
                $value = (bool) $value; // Convert to boolean
                break;
            default:
                $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // Sanitize HTML entities
                break;
        }
        return $value;
    }
}
