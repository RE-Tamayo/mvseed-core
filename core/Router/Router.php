<?php

namespace  Mvseed\Router;

use Exception;

class Router
{   
    /**
     * @var array The routing table.
     */
    private static $routes = [];

    /**
     * Adds a route to the router
     * 
     * @param string $method The HTTP method for the route (e.g. GET, POST, etc.)
     * @param string $route The URL for the route (e.g. "/users/:id")
     * @param mixed $callback The callback function for the route
     */
    public static function add($method, $route, $callback)
    {
        self::$routes[$method][$route] = $callback;
    }

    /**
     * Resolves the current request
     */
    public static function resolve()
    {
        echo self::handleRequest();
    }

    /**
     * Handles an incoming HTTP request
     * 
     * @return mixed The response from the router
     */
    public static function handleRequest()
    {
        // get the HTTP method and URL from the server request
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_SERVER['REQUEST_URI'];
        $urlParts = parse_url($url);
        $path = $urlParts['path'];

        // loop through each route for the given HTTP method
        foreach (self::$routes[$method] as $route => $callback) {
            // check if the route contains any URL parameters
            if (strpos($route, ':') !== false) {
                $routeParts = explode('/', $route);
                $pathParts = explode('/', $path);
                $params = array();
                $match = true;
                // compare each part of the route and path
                for ($i = 0; $i < count($routeParts); $i++) {
                    if ($routeParts[$i] !== $pathParts[$i]) {
                        // if this part of the route is a parameter, save its value
                        if (strpos($routeParts[$i], ':') === 0) {
                            $params[substr($routeParts[$i], 1)] = $pathParts[$i];
                        } else {
                            // otherwise, the route doesn't match
                            $match = false;
                            break;
                        }
                    }
                }
                // if all parts of the route match, call the callback with the params
                if ($match) {
                    return call_user_func_array($callback, array($params));
                }
            } else {
                // if the route is an exact match, call the callback
                if ($route === $path) {
                    if (is_callable($callback)) {
                        return call_user_func($callback);
                    } else {
                        require $callback;
                        exit;
                    }
                }
            }
        }
        // if no matching route was found, return a 404 response
        self::returnResponse(404);
    }

    /**
     * Returns an HTTP response
     * 
     * @param mixed $response The HTTP response code or message
     */
    public static function returnResponse($response)
    {
        if (is_int($response)) {
            // if the response is an HTTP status code, send it and exit
            if ($response == 404) {
                http_response_code($response);
                echo 'NOT FOUND';
                exit;
            } else {
                http_response_code($response);
                exit;
            }
        } else {
            // if the response is not an HTTP status code, throw an exception
            throw new Exception('Invalid response');
            exit;
        }
    }
}
