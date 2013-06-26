<?php
require_once(dirname(__FILE__) . '/bootstrap.php');
use Cribz\Router;
use Cribz\RouterException;

class RouterTest extends PHPUnit_Framework_TestCase {
    public function testRouter() {
        $_SERVER['argv'] = array(
            'example/cli.php',
            '/hello',
            'GET',
            'hello=world',
            'name=jim',
        );

        $_SERVER['argc'] = count($_SERVER['argv']);

        try {
            $methods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD');
            Router::any($methods, '/hello', function($request, $params) {
                echo "Hello";
            });

            foreach ($methods as $method) {
                $_SERVER['argv'][2] = $method;

                ob_start();
                Router::run();
                $response = ob_get_clean();

                $this->assertEquals("Hello", $response);
            }

        } catch (RouterException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testMiddlewareBefore() {
        $_SERVER['argv'] = array(
            'example/cli.php',
            '/before',
            'GET',
            'hello=world',
            'name=jim',
        );

        $_SERVER['argc'] = count($_SERVER['argv']);

        try {
            $methods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD');
            Router::any($methods, '/before', function($request, $params) {});

            Router::middleware('before', function($request) {
                echo "Before middleware";
            });

            foreach ($methods as $method) {
                $_SERVER['argv'][2] = $method;

                ob_start();
                Router::run();
                $response = ob_get_clean();

                $this->assertEquals("Before middleware", $response);
            }

        } catch (RouterException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testMiddlewareAfter() {
        $_SERVER['argv'] = array(
            'example/cli.php',
            '/after',
            'GET',
            'hello=world',
            'name=jim',
        );

        $_SERVER['argc'] = count($_SERVER['argv']);

        try {
            $methods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD');
            Router::any($methods, '/after', function($request, $params) {});

            Router::middleware('after', function($request) {
                echo ", After middleware";
            });

            foreach ($methods as $method) {
                $_SERVER['argv'][2] = $method;

                ob_start();
                Router::run();
                $response = ob_get_clean();

                $this->assertEquals("Before middleware, After middleware", $response);
            }

        } catch (RouterException $e) {
            $this->fail($e->getMessage());
        }
    }
}
