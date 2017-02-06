<?php
session_start();

require_once __DIR__ . './vendor/autoload.php';
require_once __DIR__ . './config/config.php';
require_once __DIR__ . './src/middleware/Welcome.php';

\Slim\Slim::registerAutoloader();

$config = array(
    'templates.path' => __DIR__ . './html/template',
    'log.level' => Config::$LOG_LEVEL,
    'log.enabled' => Config::$LOG,
    'view' => new \Slim\Views\Twig()
);

$app = new \Slim\Slim($config);
$app->add(new Welcome());

function checkPermission(\Slim\Slim $app, $rule)
{
    if (isset($_SESSION[Config::$SESSION_RULES]) && isset($_SESSION[Config::$SESSION_RULES][$rule]) && $_SESSION[Config::$SESSION_RULES][$rule]) {
        return true;
    } else {
        $app->redirect(Config::$MAIN_URL . '/home');
        return false;
    }
}

$app->group('/auth', function () use ($app) {
    require_once "./src/routing/authRouting.php";
});

$app->notFound(function () use ($app) {
    if (isset($_SESSION[Config::$SESSION_ACCOUNT])) {
        $app->redirect(Config::$MAIN_URL . '/home');
    } else {
        $app->redirect(Config::$MAIN_URL . '/logout');
    }
});

$app->run();
print "Hello";
    var_dump('auth');
die();
