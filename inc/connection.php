<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\DatabaseConnection;

$conn = (new DatabaseConnection())->getConn();
