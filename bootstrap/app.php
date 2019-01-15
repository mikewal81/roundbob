<?php

session_start();

/** Set Timezone */
date_default_timezone_set('Africa/Kampala');

/** Load Composer */
require_once __DIR__.'/../vendor/autoload.php';

/** Load Helper functions */
foreach(glob(__DIR__ . "/lib/*.php") as $file){
    // Require Once, its safer
    require_once $file;
}

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true, 
        'db' => [
            'driver'    => 'mysql',
            'host'      => 'db',
            'database'  => 'roundbob',
            'username'  => 'root',
            'password'  => 'root',
            'charset'   => 'utf8',
            'collation' => 'utf8_latvian_ci',
            'prefix' => '',
        ],
        // jwt settings
        "jwt" => [
            'secret' => 'UHDNoklvrhmVyCo2Y1Y2uadl3D7DDdoi',
        ],
        //Monolog Settings
        'logger' => [
            'name' => 'roundbob-testing-env',
            'path' => __DIR__.'/../logs/app.log',
        ]
    ],
    'response_codes' => [
        'SUCCESS' => [
            'POST'   => 200,
            'GET'    => 200,
            'CREATE' => 201,
        ],
        'ERROR' => [
            'SERVER' => 500,
            'CLIENT' => 400,
        ],
    ]
]);

/**
 *
 */
$app->options( '/{routes:.+}', function ( $request, $response, $args ) {
    return $response;
} );
/**
 *
 */
$app->add( function ( $req, $res, $next ) {
    $response = $next( $req, $res );
    return $response
        ->withHeader( 'Access-Control-Allow-Origin', '*' )
        ->withHeader( 'Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization' )
        ->withHeader( 'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS' );
} );

$container = $app->getContainer();

$container['logger'] = function ( $c ) {
    $settings = $c->get( 'settings' )['logger'];
    $logger   = new Monolog\Logger( $settings['name'] );
    $logger->pushProcessor( new Monolog\Processor\UidProcessor() );
    $logger->pushHandler( new Monolog\Handler\StreamHandler( $settings['path'] ) );
    return $logger;
};

$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['view'] = function($container){
    $view = new \Slim\Views\Twig(__DIR__.'/../resources/views', [
        'cache' => false,
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));
    $view->getEnvironment()->addGlobal(
        [ "base_url", "http://localhost:8080"],
        [ "user", $_SESSION ]
    );

    return $view;
};

/** Uploads Directory */
$container['upload_directory'] = __DIR__.'/uploads';

$container['AuthController'] = function ($container){
    return new \App\Controllers\Auth\AuthController($container);
};

$container['AdminController'] = function ($container){
    return new \App\Controllers\AdminController($container);
};

$container['ApiController'] = function ($container){
    return new \App\Controllers\ApiController($container);
};

$container['AgentController'] = function ($container){
    return new \App\Controllers\AgentController($container);
};

require __DIR__ . '/../app/routes.php';

