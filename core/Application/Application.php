<?php

namespace Mvseed\Application;

use Dotenv\Dotenv;
use Mvseed\Router\Router;

/**
 * The Application class represents the main entry point of the application.
 *
 * It loads the environment variables from the .env file located at the root of the application,
 * requires the web.php routes file, and resolves the requested route using the Router class.
 */
class Application
{

    /**
     * Runs the application.
     *
     * @param string $root The root path of the application.
     */
    public function run($root)
    {
        define('APP_PATH', $root);

        $dotenv = Dotenv::createImmutable(APP_PATH);
        $dotenv->load();

        require APP_PATH . '/' . $_ENV['ROUTES_PATH'];

        Router::resolve();
    }
}
