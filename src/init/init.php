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
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/../../config/config.yml", array(), new Igorw\Silex\ChainConfigDriver(array(new Instudies\Framework\Config\YamlCachableConfigDriver()))));

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
$app['db']->executeQuery("SET NAMES utf8;");

/************************************************************
 * Twig template engine
 */
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../../frontend/views',
));