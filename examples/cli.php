<?php
require_once(dirname(dirname(__FILE__)) . '/src/Cribz/RouterException.php');
require_once(dirname(dirname(__FILE__)) . '/src/Cribz/Router.php');

use Cribz\Router;
use Cribz\RouterException;

try {
    Router::get('/hello', function($request, $params) {
        echo "Hello\n";
    });

    Router::run();
} catch (RouterException $e) {
    echo $e->getMessage() . "\n";
}
?>
