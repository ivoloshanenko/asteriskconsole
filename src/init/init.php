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

