<?php
/**
 * Created by PhpStorm.
 * User: giapnguyen
 * Date: 2/6/17
 * Time: 06:40
 */
$app -> get('/login', function () use ($app) {
    $app->render('pages/login.twig', array());
});