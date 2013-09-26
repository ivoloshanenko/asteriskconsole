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
$app->get('/list',
    function(Request $request) use ($app) {
        $limit = 100;
        $start = (intval($request->get('page'))-1)*$limit;
        if ($start < 0) $start = 0;

        $sql = "SELECT * FROM " . $app['settings']['config']['tables']['users'] . " LIMIT ".$start.", ".$limit;
        $users = $app['db']->fetchAll($sql);

        return $app['json_response'](array('success' => true, 'list' => $users));
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
                new Assert\Length(array('min' => 1, 'max' => 15))),
            'callerid' => array(
                new Assert\NotBlank(),
                new Assert\Length(array('min' => 1, 'max' => 15))),
            'context' => array(
                new Assert\NotBlank(),
                new Assert\Length(array('min' => 1, 'max' => 30))),
            'pickupgroup' => array(
                new Assert\NotBlank(),
                new Assert\Type(array('type' => 'numeric')),
                new Assert\GreaterThan(array('value' => 0))),
            'callgroup' => array(
                new Assert\NotBlank(),
                new Assert\Type(array('type' => 'numeric')),
                new Assert\GreaterThan(array('value' => 0))),
            'nat' => array(
                new Assert\NotBlank(),
                new Assert\Choice(array('choices' => array(0, 1)))),
            'permit' => new Assert\Collection(array(
                    '0' => array(
                        new Assert\NotBlank(),
                        new Assert\Ip()),
                    '1' => array(
                        new Assert\NotBlank(),
                        new Assert\Ip()),
                ))
        ));

        $errors = $app['validator']->validateValue($user, $constraint);

        if (count($errors) > 0) {
            $errors_formated = array();
            foreach ($errors as $error) {
                array_push($errors_formated, array('user' . $error->getPropertyPath() => $error->getMessage()));
            }
            return $app['json_response'](array('errors' => $errors_formated));
        }

        $sql = "SELECT count(name) FROM " . $app['settings']['config']['tables']['users'] . " WHERE name = ?";
        $count = intval($app['db']->fetchAll($sql, array($user['name']))[0]['count(name)']);

        if ($count > 0)
            return $app['json_response'](array('errors' => array('[name]' => 'Allready used')));

        $app['db']->insert($app['settings']['config']['tables']['users'], array(
            'name' => $user['name'],
            'username' => $user['name'],
            'secret' => $user['secret'],
            'callerid' => '"'.$user['callerid'].'"<'.$user['name'].'>',
            'context' => $user['context'],
            'pickupgroup' => $user['pickupgroup'],
            'callgroup' => $user['callgroup'],
            'nat' => $user['nat'] ? 'yes' : 'no',
            'permit' => $user['permit'][0].'/'.$user['permit'][1]
        ));

        return $app['json_response'](array('success' => true));
    }
)
->method('POST');

$app->run();
