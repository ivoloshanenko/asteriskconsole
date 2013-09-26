<?php

date_default_timezone_set('UTC');

require_once __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

/************************************************************
 * Application
 */
$app = new Silex\Application();
$app['debug'] = true;

/************************************************************
 * Configs
 */
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/../../config/config.yml", array(), new Igorw\Silex\ChainConfigDriver(array(new \Igorw\Silex\YamlConfigDriver()))));

/************************************************************
 * Database connection
 */
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'dbname' =>     $app['settings']['config']['db_connection']['dbname'],
        'user' =>       $app['settings']['config']['db_connection']['user'],
        'password' =>   $app['settings']['config']['db_connection']['password'],
        'host' =>       $app['settings']['config']['db_connection']['host'],
        'driver' =>     $app['settings']['config']['db_connection']['driver'],
    ),
));
$app['db']->executeQuery("SET NAMES latin1;");

/************************************************************
 * Twig template engine
 */
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../../frontend/views',
));

/************************************************************
 * Symfony Validator
 */
$app->register(new Silex\Provider\ValidatorServiceProvider);

/************************************************************
 * Helpers
 */
$app['json_response'] = $app->share(function() use ($app) {
    return function ($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    };
});

