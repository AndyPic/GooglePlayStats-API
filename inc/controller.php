<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/connection.php');

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");


$URL = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Verify access through correct endpoint
if ($URL[1] !== "API") {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// Get request method
$method = $_SERVER["REQUEST_METHOD"];

// Check request authentication level
$apiUserHandler = new classes\handler\ApiUserHandler($conn);
$appUserHandler = new classes\handler\AppUserHandler($conn);
if ($URL[2] == "apps") {
    $auth = $appUserHandler->authenticationHandler($method);
} else {
    $auth = $apiUserHandler->authenticationHandler($method);
}

// Pass request to correct handler
if ($URL[2] == "apps") {
    if ($URL[3] == "data.php") {
        $appDataHandler = new classes\handler\AppDataHandler($conn);
        $appDataHandler->requestHandler($auth['auth'], $method);
    } else if ($URL[3] == "review.php") {
        $appReviewHandler = new classes\handler\AppReviewHandler($conn);
        $appReviewHandler->requestHandler($auth['auth'], $method);
    } else if ($URL[3] == "users.php") {
        $appUserHandler->requestHandler($auth['auth'], $method);
    } else if ($URL[3] == "year.php") {
        $appYearHandler = new classes\handler\AppYearHandler($conn);
        $appYearHandler->requestHandler($auth['auth'], $method);
    } else if ($URL[3] == "service.php") {
        $appServiceHandler = new classes\handler\AppServiceHandler($conn);
        $appServiceHandler->requestHandler($auth['auth'], $method);
    } else if ($URL[3] == "pitch.php") {
        $appPitchHandler = new classes\handler\AppPitchHandler($conn);
        $appPitchHandler->requestHandler($auth['auth'], $method);
    }
} else if ($URL[2] == "users.php") {
    $apiUserHandler->requestHandler($auth['auth'], $method);
} else {
    header("HTTP/1.1 404 Not Found");
    exit();
}
