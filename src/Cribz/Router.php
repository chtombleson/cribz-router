<?php
/*
*The MIT License (MIT)
*
* Copyright (c) 2013 Christopher Tombleson <chris@cribznetwork.com>
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/
/**
* Cribz Router
*
* @package Cribz
* @subpackage Router
* @copyright Christopher Tombleson <chris@cribznetwork.com> 2013
*/
namespace Cribz;

/**
* Router class
* Router
*
* @author Christopher Tombleson <chris@cribznetwork.com>
*/
class Router {
    /**
    * Routes
    * Used to store route info
    *
    * @access protected
    * @var array
    */
    protected static $routes = array(
        'delete'    => array(),
        'get'       => array(),
        'head'      => array(),
        'options'   => array(),
        'post'      => array(),
        'put'       => array(),
    );

    /**
    * Middleware
    * Used to store middleware info
    *
    * @access protected
    * @var array
    */
    protected static $middleware = array(
        'after'     => array(),
        'before'    => array(),
    );

    /**
    * Any
    * Set a route for any HTTP Request Method
    *
    * Supported HTTP Request Methods are: DELETE, GET, HEAD, OPTIONS, POST, PUT
    * @static
    * @access public
    * @param array  $methods    Array of HTTP Methods that can be run on the route
    * @param string $uri        Route uri
    * @param mixed  $function   Callback function, either an array or anyomous function
    * @throws RouterException
    */
    public static function any($methods, $uri, $function) {
        $validmethod = false;

        foreach ($methods as $method) {
            if (in_array(strtolower($method), array_keys(self::$routes))) {
                $validmethod = true;
            } else {
                $validmethod = false;
            }
        }

        if (!$validmethod) {
            $msg  = "Invalid HTTP method found in: " . implode(', ', $methods);
            $msg .= " valid methods are " . implode(', ', array_keys(self::$routes));
            throw new RouterException($msg);
        }

        foreach ($methods as $method) {
            self::setRoute($method, $uri, $function);
        }
    }

    /**
    * Delete
    * Set a route for DELETE HTTP Request Method
    *
    * @static
    * @access public
    * @param string $uri        Route uri
    * @param mixed  $function   Callback function, either an array or anyomous function
    * @throws RouterException
    */
    public static function delete($uri, $function) {
        self::setRoute('delete', $uri, $function);
    }

    /**
    * Exists
    * Check if a route for a given HTTP Request Method exists
    *
    * @static
    * @access public
    * @param string $method     HTTP Request Method
    * @param string $uri        Route to check
    * @return bool true if exists, otherwise false
    */
    public static function exists($method, $uri) {
        if (isset(self::$routes[strtolower($method)][$uri])) {
            return true;
        }

        return false;
    }

    /**
    * Get
    * Set a route for GET HTTP Request Method
    *
    * @static
    * @access public
    * @param string $uri        Route uri
    * @param mixed  $function   Callback function, either an array or anyomous function
    * @throws RouterException
    */
    public static function get($uri, $function) {
        self::setRoute('get', $uri, $function);
    }

    /**
    * Head
    * Set a route for HEAD HTTP Request Method
    *
    * @static
    * @access public
    * @param string $uri        Route uri
    * @param mixed  $function   Callback function, either an array or anyomous function
    * @throws RouterException
    */
    public static function head($uri, $function) {
        self::setRoute('head', $uri, $function);
    }

    /**
    * Middleware
    * Add middleware functions to be run before or after every request
    *
    * @static
    * @access public
    * @param string $when       When the middleware should be run before or after request
    * @param mixed  $function   Callback function, either an array or anyomous function
    * @throws RouterException
    */
    public static function middleware($when, $function) {
        $allowedwhen = array('before', 'after');
        
        if (!in_array(strtolower($when), $allowedwhen)) {
            throw new RouterException("Middleware can only run before or after a request");
        }

        if (!is_callable($function)) {
            throw new RouterException("Middleware function is not callable");
        }

        self::$middleware[strtolower($when)][]  = $function;
    }

    /**
    * Options
    * Set a route for OPTIONS HTTP Request Method
    *
    * @static
    * @access public
    * @param string $uri        Route uri
    * @param mixed  $function   Callback function, either an array or anyomous function
    * @throws RouterException
    */
    public static function options($uri, $function) {
        self::setRoute('options', $uri, $function);
    }

    /**
    * Post
    * Set a route for POST HTTP Request Method
    *
    * @static
    * @access public
    * @param string $uri        Route uri
    * @param mixed  $function   Callback function, either an array or anyomous function
    * @throws RouterException
    */
    public static function post($uri, $function) {
        self::setRoute('post', $uri, $function);
    }

    /**
    * Put
    * Set a route for PUT HTTP Request Method
    *
    * @static
    * @access public
    * @param string $uri        Route uri
    * @param mixed  $function   Callback function, either an array or anyomous function
    * @throws RouterException
    */
    public static function put($uri, $function) {
        self::setRoute('put', $uri, $function);
    }

    /**
    * Run
    * Run's the router and routes all uri's to the correct function
    *
    * Detirmines whether to run the cli (command line) router of HTTP router
    *
    * @static
    * @access public
    * @throws RouterException
    */
    public static function run() {
        if (php_sapi_name() == 'cli') {
            self::runCli();
        } else {
            self::runHttp();
        }
    }

    /**
    * Run Cli
    * Runs the Commandline router
    *
    * @static
    * @access public
    * @throws RouterException
    */
    public static function runCli() {
        if (php_sapi_name() != 'cli') {
            throw new RouterException("Cannot run in CLI mode when not using php cli interface");
        }

        $argc = $_SERVER['argc'];
        $argv = $_SERVER['argv'];

        if ($argc < 3 || $argv[1] == '--help') {
            echo "Useage: " . $argv[0] . " <uri> <method> [data]\n";
            echo "\turi: Uri to run\n";
            echo "\tmethod: HTTP method to use (DELETE, GET, HEAD, OPTIONS, POST, PUT)\n";
            echo "\tdata: GET or POST data eg. name=hello\n";
            exit;
        }

        $uri = $argv[1];
        $method = strtolower($argv[2]);
        $data = array();
        $params = new \stdClass();

        if ($argc > 3) {
            for ($i = 3; $i < $argc; $i++) {
                if (preg_match('#([a-zA-z0-9]+)=(.+)#', $argv[$i], $match)) {
                    $data[$match[1]] = $match[2];
                }
            }
        }

        if (!empty($data)) {
            $params->{$method} = (object) $data;
        }

        $request = (object) array(
            'request_method'    => strtoupper($method),
            'request_uri'       => $uri,
            'remote_address'    => '127.0.0.1',
            'user_agent'        => 'cli',
            'http_referer'      => 'cli',
            'https'             => false,
        );

        if (!empty(self::$routes[$method])) {
            foreach (self::$routes[$method] as $url => $function) {
                $muri = preg_replace('#:([a-z A-Z 0-9]+)#', '(.+)', $url);

                if (preg_match('#^' . $muri . '$#', $uri)) {
                    $params->uri = self::parseUri($url, $uri);

                    if (!empty(self::$middleware['before'])) {
                        foreach (self::$middleware['before'] as $before) {
                            if (is_array($before)) {
                                call_user_func_array($before, array($request));
                            } else {
                                $before($request);
                            }
                        }
                    }

                    if (is_array(self::$routes[$method][$url])) {
                        $callback = call_user_func_array(self::$routes[$method][$url], array($request, $params));
                    } else {
                        $func = self::$routes[$method][$url];
                        $callback =  $func($request, $params);
                    }

                    if (!empty(self::$middleware['after'])) {
                        foreach (self::$middleware['after'] as $after) {
                            if (is_array($after)) {
                                call_user_func_array($after, array($request));
                            } else {
                                $after($request);
                            }
                        }
                    }

                    return $callback;
                }
            }

            throw new RouterException("No route found for: " . strtoupper($method) . ", " . $url, 404);
        } else {
            throw new RouterException("No routes have been defined", 500);
        }
    }

    /**
    * Run HTTP
    * Runs HTTP Router
    *
    * @static
    * @access public
    * @throws RouterException (Exception code relates to an HTTP Status code)
    */
    public static function runHttp() {
        $rmethod = strtolower($_SERVER['REQUEST_METHOD']);
        $ruri = $_SERVER['REQUEST_URI'];
        $params = self::processParams();
        $request = self::buildRequest();

        if (!empty(self::$routes[$rmethod])) {
            foreach (self::$routes[$rmethod] as $uri => $function) {
                $muri = preg_replace('#:([a-z A-Z 0-9]+)#', '(.+)', $uri);

                if (preg_match('#^' . $muri . '$#', $ruri)) {
                    $params->uri = self::parseUri($uri, $ruri);

                    if (!empty(self::$middleware['before'])) {
                        foreach (self::$middleware['before'] as $before) {
                            if (is_array($before)) {
                                call_user_func_array($before, array($request));
                            } else {
                                $before($request);
                            }
                        }
                    }

                    if (is_array(self::$routes[$rmethod][$uri])) {
                        $callback = call_user_func_array(self::$routes[$rmethod][$uri], array($request, $params));
                    } else {
                        $func = self::$routes[$rmethod][$uri];
                        $callback = $func($request, $params);
                    }

                    if (!empty(self::$middleware['after'])) {
                        foreach (self::$middleware['after'] as $after) {
                            if (is_array($before)) {
                                call_user_func_array($after, array($request));
                            } else {
                                $after($request);
                            }
                        }
                    }

                    return $callback;
                }
            }

            throw new RouterException("No route found for: " . strtoupper($rmethod) . ", " . $ruri, 404);
        } else {
            throw new RouterException("No routes have been defined", 500);
        }
    }

    /**
    * Set Header
    * Sets a HTTP Header
    *
    * @static
    * @access public
    * @param string $header     Header to set
    */
    public static function setHeader($header) {
        header($header);
    }

    /**
    * Set Headers
    * Set multiple HTTP headers
    *
    * @static
    * @access public
    * @param array $headers     Array of headers to set
    */
    public static function setHeaders($headers) {
        foreach ($headers as $header) {
            self::setHeader($header);
        }
    }

    /**
    * Build Request
    * Builds the request object that is passed to the routes callback function
    *
    * @static
    * @access private
    * @return object with request data
    */
    private static function buildRequest() {
        $request = (object) array(
            'request_method'    => $_SERVER['REQUEST_METHOD'],
            'request_uri'       => $_SERVER['REQUEST_URI'],
            'remote_address'    => $_SERVER['REMOTE_ADDR'],
            'user_agent'        => $_SERVER['HTTP_USER_AGENT'],
            'http_referer'      => empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'],
            'https'             => empty($_SERVER['HTTPS']) ? false : true,
        );

        return $request;
    }

    /**
    * Parse Uri
    * Parses any placeholders in the route
    *
    * @static
    * @access private
    * @param string $routeuri       The routes uri
    * @param string $requesturi     The request uri from $_SERVER['REQUEST_URI']
    * @return object with any placeholder values
    */
    private static function parseUri($routeuri, $requesturi) {
        preg_match_all('#:([^/]+|.+)#', $routeuri, $names);
        $reguri = preg_replace('#:([^/]+|.+)#', '(.+)', $routeuri);
        preg_match('#^' . $reguri . '$#', $requesturi, $values);
        $params = new \stdClass();

        if (!empty($names) && !empty($values)) {
            foreach ($names[1] as $key => $name) {
                $params->{$name} = $values[$key + 1];
            }
        }

        return $params;
    }

    /**
    * Process Params
    * Processes any $_GET and $_POST data
    *
    * @static
    * @access private
    * @return object with $_GET and $_POST data
    */
    private static function processParams() {
        $params = new \stdClass();
        $params->get = new \stdClass();
        $params->post = new \stdClass();

        foreach ($_GET as $key => $value) {
            $params->get->{$key} = $value;
        }

        foreach ($_POST as $key => $value) {
            $params->post->{$key} = $value;
        }

        return $params;
    }

    /**
    * Set Route
    * Set a route
    *
    * @static
    * @access private
    * @param string $method     HTTP Request Method
    * @param string $uri        Route uri
    * @param mixed  $function   Callback function, either an array or anyomous function
    * @throws RouterException
    */
    private static function setRoute($method, $uri, $function) {
        if (self::exists($method, $uri)) {
            throw new RouterException("URI: " . $uri . " is already defined for " . strtoupper($method) . " method");
        }

        if (!is_callable($function)) {
            throw new RouterException("Callback function is not callable");
        }

        self::$routes[strtolower($method)][$uri] = $function;
    }
}
