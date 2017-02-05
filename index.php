<?php
session_start();

require_once __DIR__ . './vendor/autoload.php';
require_once __DIR__ . './config/config.php';
require_once __DIR__ . './src/middleware/Welcome.php';

$config = array(
    'templates.path' => __DIR__ . './html/template',
    'log.level' => Config::$LOG_LEVEL,
    'log.enabled' => Config::$LOG,
    'view' => new Slim\Views\Twig()
);

$app = new \Slim\Slim($config);
$app -> add(new Welcome());

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

$app->run();