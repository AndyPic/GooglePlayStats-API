<?php

namespace classes\handler;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/connection.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to handle api user requests 
 */
class ApiUserHandler
{
    // Instance Vars

    private $accessApiUser;
    private $conn;

    // Constructors

    /**
     * Constructor with args
     */
    public function __construct($conn)
    {
        $this->accessApiUser = new \classes\access\ApiUserAccess($conn);
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

                    if (isset($_GET['api_user_data'], $_GET['api_user_id']) && $_GET['api_user_data'] != "") {
                        $user = $_GET['user_data'];
                        $user_data = $this->getUserData($user);

                        $requester = $_GET['api_user_id'];
                        // Users with read rights may only view THEIR OWN details.
                        if ($user_data[0]['id'] == $requester || $user_data[0]['email'] == $requester) {
                            $return = $user_data;
                        } else if ($auth > 2) {
                            $return = $user_data;
                        } else {
                            $return = json_encode(Utility::invalidAuthReturn());
                        }
                    }
                    break;

                case "POST": // INSERT requests

                    // Guard clause for authorisation
                    if ($auth < 4 ) {
                        $return = json_encode(Utility::invalidAuthReturn());
                        break;
                    }

                    if (isset($_POST['level']) && isset($_POST['email'])) {
                        $values = [
                            "level" => $_POST['level'],
                            "email" => $_POST['email'],
                        ];
                        $return = $this->createUser($values);
                    }
                    break;

                case "PUT": // UPDATE requests

                    // Get PUT data in to associative array
                    parse_str(file_get_contents("php://input"), $put_vars);
                    $updated = 0;

                    // Guard clause for authorisation
                    if ($auth < 3 || $put_vars['target_id'] != $put_vars['api_user_id']) {
                        $return = json_encode(Utility::invalidAuthReturn());
                        break;
                    }

                    // Would be able to do this in a single call if using PDO instead of mysqli
                    if (isset($put_vars['email']) && isset($put_vars['target_id'])) {
                        $email = ["email" => $put_vars['email']];
                        $api_user_id = $put_vars['target_id'];
                        $updated += $this->updateUserData($api_user_id, $email);
                    }

                    if (isset($put_vars['level']) && isset($put_vars['target_id'])) {
                        $level = ["level" => $put_vars['level']];
                        $api_user_id = $put_vars['target_id'];
                        $updated += $this->updateUserData($api_user_id, $level);
                    }

                    $return = $updated . " entries updated.";
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
                        $api_user_id = $del_vars['delete_user'];
                        $return = $this->deleteUser($api_user_id);
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
                if (isset($_GET['api_user_id']) && $_GET['api_user_id'] != null && isset($_GET['sig']) && $_GET['sig'] != null) {
                    $return = $this->getAuthentication($_GET['api_user_id'], $_GET, $_GET['sig']);
                    break;
                }
                

            case "POST": // INSERT requests
                if (isset($_POST['api_user_id']) && $_POST['api_user_id'] != null && isset($_POST['sig']) && $_POST['sig'] != null) {
                    $return = $this->getAuthentication($_POST['api_user_id'], $_POST, $_POST['sig']);
                    break;
                }
                

            case "PUT": // UPDATE requests
                parse_str(file_get_contents("php://input"), $put_vars);

                if (isset($put_vars['api_user_id']) && $put_vars['api_user_id'] != null && isset($put_vars['sig']) && $put_vars['sig'] != null) {
                    $return = $this->getAuthentication($put_vars['api_user_id'], $put_vars, $put_vars['sig']);
                    break;
                }
                

            case "DELETE": // DELETE requests
                parse_str(file_get_contents("php://input"), $del_vars);

                if (isset($del_vars['api_user_id']) && $del_vars['api_user_id'] != null && isset($del_vars['sig']) && $del_vars['sig'] != null) {
                    $return = $this->getAuthentication($del_vars['api_user_id'], $del_vars, $del_vars['sig']);
                    break;
                }
                

            default:
                $response['status'] = "success";
                $response['auth'] = 0;
                $return = $response;
        }
        return $return;
    }

    private function deleteUser($api_user_id)
    {
        $response = $this->accessApiUser->deleteUser($api_user_id);
        return json_encode($response);
    }

    private function getAuthentication($api_user_id, $inputs, $sig)
    {
        $auth = $this->accessApiUser->authenticateRequest($api_user_id, $inputs, $sig);
        return $auth;
    }

    private function createUser($values)
    {
        $return = $this->accessApiUser->createUser($values);
        return json_encode($return);
    }

    private function getUserData($api_user_id)
    {
        $user_data = $this->accessApiUser->userData($api_user_id);
        return json_encode($user_data);
    }

    private function updateUserData($api_user_id, $input)
    {
        $user_data = $this->accessApiUser->updateUserData($api_user_id, $input);
        return json_encode($user_data);
    }
}
