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
        $q = $request->get('q');

        $sql = "SELECT * FROM " . $app['settings']['config']['tables']['users'] . " LIMIT ".$start.", ".$limit;
        if ($q) $sql = "SELECT * FROM " . $app['settings']['config']['tables']['users'] . " WHERE (name LIKE '%".$q."%' OR secret LIKE '%".$q."%' OR callerid LIKE '%".$q."%' OR context LIKE '%".$q."%' OR pickupgroup LIKE '%".$q."%' OR callgroup LIKE '%".$q."%' OR nat LIKE '%".$q."%' OR permit LIKE '%".$q."%') LIMIT ".$start.", ".$limit;

        $users = $app['db']->fetchAll($sql);

        return $app['json_response'](array('success' => true, 'list' => $users));
    }
)
->method('GET');

/**
 * Create
 */
$app->get('/remove',
    function(Request $request) use ($app) {
        $user = intval($request->get('user'));
        $app['db']->delete($app['settings']['config']['tables']['users'], array($user));
        return $app['json_response'](array('success' => true));
    }
)
->method('DELETE|POST');

/**
 * Create or edit
 */
$app->get('/post',
    function(Request $request) use ($app) {

        $user = $request->get('user');

        $constraint = new Assert\Collection(array(
            'id' => array(),
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
                array_push($errors_formated, array('field' => 'user' . $error->getPropertyPath(), 'text' => $error->getMessage()));
            }
            return $app['json_response'](array('errors' => $errors_formated));
        }

        if (isset($user['id']) && $user['id']) {
            /** Edition */

            $sql = "SELECT count(name) FROM " . $app['settings']['config']['tables']['users'] . " WHERE name = ? AND id <> ?";
            $count = intval($app['db']->fetchAll($sql, array($user['name'], $user['id']))[0]['count(name)']);

            if ($count > 0)
                return $app['json_response'](array('errors' => array('field' => 'user[name]', 'text' => 'Allready used')));

            $app['db']->update($app['settings']['config']['tables']['users'], array(
                'name' => $user['name'],
                'username' => $user['name'],
                'secret' => $user['secret'],
                'callerid' => '"'.$user['callerid'].'"<'.$user['name'].'>',
                'context' => $user['context'],
                'pickupgroup' => $user['pickupgroup'],
                'callgroup' => $user['callgroup'],
                'nat' => $user['nat'] ? 'yes' : 'no',
                'permit' => $user['permit'][0].'/'.$user['permit'][1]
            ), array('id' => $user['id']));

            $id = $user['id'];

        } else {
            /** Creation */

            $sql = "SELECT count(name) FROM " . $app['settings']['config']['tables']['users'] . " WHERE name = ?";
            $count = intval($app['db']->fetchAll($sql, array($user['name']))[0]['count(name)']);

            if ($count > 0)
                return $app['json_response'](array('errors' => array(array('field' => 'user[name]', 'text' => 'Allready used'))));

            $user = $app['db']->insert($app['settings']['config']['tables']['users'], array(
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

            $id = $app['db']->lastInsertId();

        }

        $sql = "SELECT * FROM " . $app['settings']['config']['tables']['users'] . " WHERE id = ?";
        $user = $app['db']->fetchAll($sql, array($id))[0];

        return $app['json_response'](array('success' => true, 'user' => $user));
    }
)
->method('POST');

$app->run();
