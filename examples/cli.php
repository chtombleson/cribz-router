<?php
require_once(dirname(dirname(__FILE__)) . '/src/Cribz/RouterException.php');
require_once(dirname(dirname(__FILE__)) . '/src/Cribz/Router.php');

use Cribz\Router;
use Cribz\RouterException;

try {
    Router::middleware('before', function($request) {
        echo "Before middleware\n";
    });

    Router::middleware('after', function($request) {
        echo "After middleware\n";
    });

    Router::get('/hello', function($request, $params) {
        echo "Hello\n";
    });

    Router::get('/hi/:name', function($request, $params) {
        echo "Hello, " . $params->uri->name . "\n";
    });

    Router::post('/test', function($request, $params) {
        print_r($params->post);
    });

    Router::run();
} catch (RouterException $e) {
    echo $e->getMessage() . "\n";
}
?>
