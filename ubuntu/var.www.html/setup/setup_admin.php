#!/usr/bin/env php
<?php
$adminUsername = getenv('APP_ADMIN');
$adminPassword = getenv('APP_ADMINPASS');
if ($adminUsername && $adminPassword) {
    require_once __DIR__ . '/../vendor/autoload.php';

    define('BASE_DIR', realpath(__DIR__ . '/../'));

    $userService = \Htec\Service\User::getInstance();

    $userService->create([
        'id' => 1,
        'firstName' => 'Admin',
        'lastName' => 'Admin',
        'username' => $adminUsername,
        'password' => $adminPassword,
        'role' => 'admin',
    ]);
}

