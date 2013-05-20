# Cribz\Router
PHP 5 routing library.

This library can be used to define routes/uri's and assign
a callback function that is executed when the route is hit.

This library works for both CLI(Command Line) and in web applications.

## Examples
There is an example cli file in the examples directory (examples/cli.php).
Try it by using the following command `$php examples/cli.php /hello GET`

## API
**Router::any(array $methods, string $uri, callback $function)**
Set a route for multiple HTTP request methods.

**Router::delete(string $uri, callback $function)**
Set a route for a HTTP Delete request.

**Router::exists(string $method, string $uri)**
Check if a route exists.

**Router::get(string $uri, callback $function)**
Set a route for a HTTP Get request.

**Router::head(string $uri, callback $function)**
Set a route for a HTTP Head request.

**Router::options(string $uri, callback $function)**
Set a route for a HTTP Options request.

**Router::post(string $uri, callback $function)**
Set a route for a HTTP Post request.

**Router::put(string $uri, callback $function)**
Set a route for a HTTP Put request

**Router::run()**
Run the routes. Wraps both runCli() & runHttp()

**Router::runCli()**
Run routes from the Command Line.

**Router::runHttp()**
Run routes from a HTTP request.

## Test
To run the test run `$phpunit`

## License
See LICENSE
