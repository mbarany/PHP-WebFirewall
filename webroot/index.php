<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

// App Debugging
$app['debug'] = getenv('DEBUG') == '1';

// Session
$app->register(new Silex\Provider\SessionServiceProvider());
// Use Twig Templates
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

// Dependencies
$app['dataStore'] = new Barany\WebFirewall\JsonStore(__DIR__ . '/../dist/store.json');
$app['ipTablesManager'] = new Barany\WebFirewall\IpTablesManager($app['dataStore'], [
    'distDir' => __DIR__ . '/../dist',
    'chainName' => 'WEB-FIREWALL',
]);

/**
 * Routes
 */

$app->match('/login', function (Request $request) use ($app) {
    $loginError = false;
    if ($request->isMethod(Request::METHOD_POST)) {
        $user = $request->request->get('user');
        $pass = $request->request->get('pass');
        $users = $app['dataStore']->get('users', []);
        if (isset($users[$user]) && password_verify($pass, $users[$user])) {
            $app['session']->set('user', $user);
            return $app->redirect($app['url_generator']->generate('home'));
        }
        $loginError = true;
    }
    return $app['twig']->render('login.html.twig', [
        'loginError' => $loginError
    ]);
})->method('GET|POST')->bind('login');

$app->get('/logout', function () use ($app) {
    $app['session']->remove('user');
    return $app->redirect($app['url_generator']->generate('login'));
})->bind('logout');

$app->get('/', function (Request $request) use ($app) {
    if (!$app['session']->get('user')) {
        return $app->redirect($app['url_generator']->generate('login'));
    }
    $showApplyTime = $request->query->has('flag');
    return $app['twig']->render('index.html.twig', [
        'basePath' => $request->getBasePath(),
        'ip' => $request->getClientIp(),
        'rules' => $app['ipTablesManager']->getAllRules(),
        'applyTime' => $showApplyTime ? 60 - intval(date("s")) : NULL,
    ]);
})->bind('home');

// API Routes

$app->get('/api/firewall/rules', function () use ($app) {
    return $app->json($app['ipTablesManager']->getAllRules());
});

$app->post('/api/firewall/rules', function (Request $request) use ($app) {
    $app['ipTablesManager']->addRule(
        $request->request->get('service'),
        $request->request->get('host'),
        $request->request->get('description')
    );
    return new Response('', Response::HTTP_NO_CONTENT);
});

$app->delete('/api/firewall/rules/{id}', function ($id) use ($app) {
    $app['ipTablesManager']->deleteRule($id);
    return new Response('', Response::HTTP_NO_CONTENT);
});

$app->run();
