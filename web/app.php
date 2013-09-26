<?php

require_once __DIR__.'/../src/init/init.php';

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

/**
 * Home page
 */
$app->get('/',
    function(Request $request) use ($app) {
        return $app['twig']->render('home', array());
    }
)
->method('GET');

$app->run();
