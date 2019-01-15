<?php
/**
 * Routes
 */
$app->options( '/{routes:.+}', function ( $request, $response, $args ) {
    return $response;
} );
/**
 * CORS
 */
$app->add( function ( $req, $res, $next ) {
    $response = $next( $req, $res );
    return $response
        ->withHeader( 'Access-Control-Allow-Origin', '*' )
        ->withHeader( 'Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization' )
        ->withHeader( 'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS' );
} );
/** 
 * Tuupola JWT
 */
$app->add(new \Tuupola\Middleware\JwtAuthentication([
    "path"        => ["/api", "/admin", "/agent"],
    "attribute"  => "token_data",
    "secret"     => 'UHDNoklvrhmVyCo2Y1Y2uadl3D7DDdoi',
    "algorithim" => ["HS256"],
    "error" => function ($res, $args){
        $data["status"] = "error";
        $data["message"] = $args['message'];
        return $response
                ->withHeader("Content-Type", "application/json")
                ->withJson($data, JSON_UNESCAPED_SLASHES | JSON_PRETT_PRINT);
    }
]));
