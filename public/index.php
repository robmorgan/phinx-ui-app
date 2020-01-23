<?php
declare(strict_types=1);

use App\Application\Handlers\HttpErrorHandler;
use App\Application\Handlers\ShutdownHandler;
use App\Application\ResponseEmitter\ResponseEmitter;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

// Automatically execute Phinx migrations in production on app startup
// This means the first request after a deployment will likely be slower!
if (!getenv('ENVIRONMENT') || getenv('ENVIRONMENT') == 'production') {
    $phinxEnv = getenv('ENVIRONMENT') ? getenv('ENVIRONMENT') : 'production';
    $phinxTarget = null; // apply all available migrations
    $options = [
		    'configuration' => __DIR__ . '/../phinx.php',
    ];
    $phinxApp = new Phinx\Console\PhinxApplication();
    $wrapper = new Phinx\Wrapper\TextWrapper($phinxApp, $options);
    $output = call_user_func([$wrapper, 'getMigrate'], $phinxEnv, $phinxTarget);
    $error = $wrapper->getExitCode() > 0;
}

// Define app routes
$app->get('/', function (Request $request, Response $response, $args) use ($container) {
    $db = $container->get(PDO::class);
    $query = $db->query("SELECT * FROM phinxlog");
    $args['migrations'] = $query->fetchAll();

    $renderer = new PhpRenderer(__DIR__ . '/../templates');
  return $renderer->render($response, "index.phtml", $args);
});

/** @var bool $displayErrorDetails */
$displayErrorDetails = $container->get('settings')['displayErrorDetails'];

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
