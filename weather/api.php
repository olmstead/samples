<?php

// Base classes
require_once __DIR__.'/lib/DB.php';
require_once __DIR__.'/lib/Persistable.php';
require_once __DIR__.'/lib/Entity.php';

// Model classes
require_once __DIR__.'/lib/Location.php';
require_once __DIR__.'/lib/Day.php';

// Controller
require_once __DIR__.'/lib/Controller.php';
$controller = new Controller();

 // Route the request
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $controller->getAction();
        break;
    case 'POST':
        $controller->upsertAction();
        break;
    case 'DELETE':
        $controller->deleteAction();
        break;    
    default:
        break;
}
                