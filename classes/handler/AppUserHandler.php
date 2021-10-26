<?php

namespace classes\handler;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/connection.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to handle app user requests 
 */
class AppUserHandler
{
    // Instance Vars

    private $accessAppUser;
    private $conn;

    // Constructors

    /**
     * Constructor with args
     */
    public function __construct($conn)
    {
        $this->accessAppUser = new \classes\access\AppUserAccess($conn);
        $this->conn = $conn;
    }

    // Methods

    public function requestHandler($auth, $method)
    {
        // Default return
        $return = json_encode(Utility::defaultReturn());


        // Check request method
        switch ($method) {
            case "GET": // SELECT requests

                if (isset($_GET['validate_email'])) {
                    $email = $_GET['validate_email'];
                    $return = $this->getValidateEmail($email);
                    break;
                }
                if (isset($_GET['email'])) {
                    $email = $_GET['email'];
                    $return = $this->getUserData($email);
                }

                if (isset($_GET['app_user_id'])) {
                    $app_user_id = $_GET['app_user_id'];
                    $return = $this->getUserData($app_user_id);
                }
                break;

            case "POST": // INSERT requests


                if (isset($_POST['level'], $_POST['email'], $_POST['display_name'], $_POST['password'])) {
                    $values = array();
                    array_push($values, $_POST['email'], $_POST['display_name'], $_POST['password'], $_POST['level']);
                    $return = $this->createUser($values);
                }
                break;

            case "PUT": // UPDATE requests

                // Get PUT data in to associative array
                parse_str(file_get_contents("php://input"), $put_vars);

                if (isset($put_vars['target_id'])) {
                    if (isset($put_vars['level']) || isset($put_vars['email']) || isset($put_vars['display_name']) || isset($put_vars['password'])) {
                        $return = $this->updateUserData($put_vars);
                    }
                }
                break;

            case "DELETE": // DELETE requests

                // Guard clause for authorisation
                if ($auth < 4) {
                    $return = json_encode(Utility::invalidAuthReturn());
                    break;
                }

                // Get DELETE data in to associative array
                parse_str(file_get_contents("php://input"), $del_vars);

                if (isset($del_vars['delete_user'])) {
                    $app_user_id = $del_vars['delete_user'];
                    $return = $this->deleteUser($app_user_id);
                }
                break;
        }

        echo $return;
    }


    /**
     * Method to handle authentication
     */
    public function authenticationHandler($method): array
    {
        switch ($method) {
            case "GET": // SELECT requests
                if (isset($_GET['app_user_id']) && $_GET['app_user_id'] != null && isset($_GET['sig']) && $_GET['sig'] != null) {
                    $return = $this->getAuthentication($_GET['app_user_id'], $_GET, $_GET['sig']);
                    break;
                }


            case "POST": // INSERT requests
                if (isset($_POST['app_user_id']) && $_POST['app_user_id'] != null && isset($_POST['sig']) && $_POST['sig'] != null) {
                    $return = $this->getAuthentication($_POST['app_user_id'], $_POST, $_POST['sig']);
                    break;
                }


            case "PUT": // UPDATE requests
                parse_str(file_get_contents("php://input"), $put_vars);

                if (isset($put_vars['app_user_id']) && $put_vars['app_user_id'] != null && isset($put_vars['sig']) && $put_vars['sig'] != null) {
                    $return = $this->getAuthentication($put_vars['app_user_id'], $put_vars, $put_vars['sig']);
                    break;
                }


            case "DELETE": // DELETE requests
                parse_str(file_get_contents("php://input"), $del_vars);

                if (isset($del_vars['app_user_id']) && $del_vars['app_user_id'] != null && isset($del_vars['sig']) && $del_vars['sig'] != null) {
                    $return = $this->getAuthentication($del_vars['app_user_id'], $del_vars, $del_vars['sig']);
                    break;
                }


            default:
                $response['status'] = "success";
                $response['auth'] = 0;
                $return = $response;
        }
        return $return;
    }

    private function getAuthentication($app_user_id, $inputs, $sig)
    {
        $auth = $this->accessAppUser->authenticateRequest($app_user_id, $inputs, $sig);
        return $auth;
    }

    private function deleteUser($app_user_id)
    {
        $response = $this->accessAppUser->deleteUser($app_user_id);
        return json_encode($response);
    }

    private function createUser($values)
    {
        $return = $this->accessAppUser->createUser($values);
        return json_encode($return);
    }

    private function getUserData($user)
    {
        $user_data = $this->accessAppUser->userData($user);
        return json_encode($user_data);
    }

    private function getValidateEmail($user)
    {
        $user_data = $this->accessAppUser->validateEmail($user);
        return json_encode($user_data);
    }

    private function updateUserData($inputs)
    {
        $user_data = $this->accessAppUser->updateUserData($inputs);
        return json_encode($user_data);
    }
}
