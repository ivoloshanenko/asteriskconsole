<?php

require_once __DIR__.'/../src/init/init.php';

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Validator\Constraints as Assert;

/**
 * Home page
 */
$app->get('/',
    function(Request $request) use ($app) {
        return $app['twig']->render('home.twig', array());
    }
)
->method('GET');

/**
 * Create
 */
$app->get('/create',
    function(Request $request) use ($app) {

        $user = $request->get('user');

        $constraint = new Assert\Collection(array(
            'name' => array(
                new Assert\NotBlank(),
                new Assert\Length(array('min' => 1, 'max' => 10))),
            'secret' => array(
                new Assert\NotBlank(),
                new Assert\Length(array('min' => 15, 'max' => 15))),
            'callerid' => array(
                new Assert\NotBlank(),
                new Assert\Length(array('min' => 15, 'max' => 15)))
        ));

        $errors = $app['validator']->validateValue($user, $constraint);

        return $app['json_response'](array('success' => true));
    }
)
->method('GET');

$app->run();
