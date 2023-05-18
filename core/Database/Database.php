<?php

namespace Mvseed\Database;

use PDO;
use PDOException;

/**
 * Class Database
 * A class for connecting to a MySQL database using PDO.
 */
class Database
{

    /**
     * @var string The database server hostname.
     */
    private static $host;

    /**
     * @var string The database name.
     */
    private static $db_name;

    /**
     * @var string The database username.
     */
    private static $username;

    /**
     * @var string The database password.
     */
    private static $password;

    /**
     * @var PDO|null The PDO connection object.
     */
    private static $conn;

    /**
     * Connect to the database using PDO.
     *
     * @return PDO|null Returns the PDO connection object, or null if the connection fails.
     */
    public static function connect()
    {
        // Get the connection parameters from the environment variables
        self::$host = $_ENV['DB_HOST'];
        self::$db_name = $_ENV['DB_NAME'];
        self::$username = $_ENV['DB_USER'];
        self::$password = $_ENV['DB_PASS'];

        try {
            // Attempt to create a new PDO connection
            self::$conn = new PDO(
                'mysql:host=' . self::$host . ';dbname=' . self::$db_name,
                self::$username,
                self::$password
            );
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // If the connection fails, log the error message and return null
            error_log('Connection Error: ' . $e->getMessage());
            self::$conn = null;
        }

        // Return the PDO connection object
        return self::$conn;
    }

    /**
     * Disconnect from the database.
     */
    public static function disconnect()
    {
        self::$conn = null;
    }
}
