<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dataStore = new \Barany\WebFirewall\JsonStore(__DIR__ . '/../dist/store.json');

$users = $dataStore->get('users', []);

function addUser($user) {
    $password = promptPassword();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 15]);
    return [$user, $hashedPassword];
}

function promptPassword() {
    $command = "/usr/bin/env bash -c 'echo OK'";
    if (rtrim(shell_exec($command)) !== 'OK') {
        trigger_error("Can't invoke bash");
        return NULL;
    }
    $command = "/usr/bin/env bash -c 'read -s -p \""
        . 'Enter Password:'
        . "\" mypassword && echo \$mypassword'";
    $password = rtrim(shell_exec($command));
    echo "\n";
    return $password;
}

switch ($argv[1]) {
    case 'add':
        list($user, $pass) = addUser($argv[2]);
        $users[$user] = $pass;
        $dataStore->set('users', $users);
        echo 'Done.';
        break;
    default:
        echo 'Unknown command!' . "\n";
        break;
}

echo 'GoodBye.';
