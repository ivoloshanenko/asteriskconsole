<?php

require_once __DIR__.'/../src/init/init.php';

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

$app->get('/',
    function(Request $request) use ($app) {
    	echo 'Hello !';
        exit();
    }
)
->method('GET');

$app->run();
