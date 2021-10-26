<?php

namespace classes\access;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\ApiKey;
use classes\Utility;

/**
 * Class to access app user data
 */
class AppUserAccess
{

    // Instance Vars
    private $conn;

    // Constructors

    /**
     * Constructor with arg
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Methods

    /**
     * Method to create a new user, if successful, returns the new user details
     */
    public function createUser($params): array
    {
        $query = "INSERT INTO APP_users (id, email, display_name, password, user_level_id, api_key)
                VALUES (NULL, ?, ?, ?, ?, ?);";
        $response = array();
        $types = "sssis";

        $params[] = ApiKey::generateKey();

        $stmt = $this->conn->prepare($query);
        // Exit + error message on failed connection
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        // Hash password
        $params[2] = password_hash($params[2], PASSWORD_BCRYPT);

        $response = array_merge($response, Utility::fetchResponse($this->conn, $query, @$params, @$types));
        return $response;
    }

    public function deleteUser($app_user_id): array
    {
        $query = "DELETE FROM APP_users WHERE APP_users.id = ?;";

        $stmt = $this->conn->prepare($query);
        // Exit + error message on failed connection
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        $stmt->bind_param("i", $app_user_id);
        $stmt->execute();

        if ($this->conn->affected_rows > 0) {
            $response['status'] = "success";
            return $response;
        } else {
            return Utility::defaultReturn();
        }
    }

    /**
     * Get user data from user ID
     * Optional second param ($raw), if set to TRUE returns raw data (no inner joins)
     */
    public function userData($input, $raw = false): array
    {   // Would be easier with PDO rather than mysqli

        if (is_numeric($input)) {
            $search = "id";
            $type = "i";
        } else {
            $search = "email";
            $type = "s";
        }

        if ($raw) {
            $query = "SELECT id, email, display_name, password, user_level_id FROM APP_users WHERE {$search} = ?;";
        } else {
            $query = "SELECT APP_users.id, email, display_name, password, SERVICE_user_level.level AS user_level
                FROM (APP_users
                INNER JOIN SERVICE_user_level ON APP_users.user_level_id = SERVICE_user_level.id)
                WHERE APP_users.{$search} = ?;";
        }

        $stmt = $this->conn->prepare($query);
        // Exit + error message on failed connection
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        $stmt->bind_param($type, $input);
        $stmt->execute();
        $result = $stmt->get_result();
        $response['status'] = "success";
        $response['data'] = $result->fetch_all(MYSQLI_ASSOC);

        return $response;
    }

    public function validateEmail($input)
    {

        $query = "SELECT id, email, display_name FROM APP_users WHERE email = ?;";

        $stmt = $this->conn->prepare($query);
        // Exit + error message on failed connection
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        $stmt->bind_param("s", $input);
        $stmt->execute();
        $result = $stmt->get_result();
        $response['status'] = "success";
        $holder = $result->fetch_all(MYSQLI_ASSOC);

        if (count($holder) > 0) {
            $response['data'] = $holder;
        } else {
            $response['data'] = "invalid";
        }

        return $response;
    }

    // private function to get user api key
    private function getUserKey($app_user_id)
    {
        $query = "SELECT api_key, user_level_id FROM APP_users WHERE APP_users.id = ?";
        $params[] = $app_user_id;
        $types = "i";

        $response = Utility::fetchResponse($this->conn, $query, $params, $types);
        return $response;
    }

    public function authenticateRequest($app_user_id, $inputs, $sig): array
    {
        // Get user data
        $user_data = $this->getUserKey($app_user_id);

        // Exit if invalid user ID
        if ($user_data == null) {
            return Utility::defaultReturn();
        }

        // Remove authentication info from input, to get only request
        unset($inputs['app_user_id']);
        unset($inputs['sig']);

        // Exit if no param given
        if (empty($inputs)) {
            return Utility::defaultReturn();
        }

        $expected_sig = ApiKey::generateSignature($user_data['data'][0]['api_key'], $inputs);

        // URL decoded
        $sig = urldecode($sig);

        $response['status'] = "success";

        if ($expected_sig == $sig) {
            $response['auth'] = $user_data['data'][0]['user_level_id'];
        } else {
            $response['auth'] = 0;
        }
        return $response;
    }

    public function updateUserData($inputs): array
    {   // Would be easier with PDO rather than mysqli

        $num_updated = 0;
        $array_keys = array_keys($inputs);

        for ($loop = 0; $loop < count($inputs); $loop++) {

            $invalid = false;

            switch ($array_keys[$loop]) {
                case "email":
                    $name = "email";
                    $type = "si";
                    break;
                case "display_name":
                    $name = "display_name";
                    $type = "si";
                    break;
                case "password":
                    $name = "password";
                    $type = "si";
                    // Hash password
                    $inputs[$array_keys[$loop]] = password_hash($inputs[$array_keys[$loop]], PASSWORD_BCRYPT);
                    break;
                case "level":
                    $name = "user_level_id";
                    $type = "ii";
                    break;

                default:
                    $invalid = true;
                    break;
            }

            // If an input invalid, skip that iteration 
            if ($invalid == true) {
                continue;
            }

            $query = "UPDATE APP_users
                SET " . $name . " = ?
                WHERE id = ?";

            $stmt = $this->conn->prepare($query);
            // Exit + error message on failed connection
            if (!$stmt) {
                return Utility::errorReturn($this->conn->error);
            }

            $stmt->bind_param($type, $inputs[$array_keys[$loop]], $inputs['target_id']);
            $stmt->execute();

            $num_updated += $this->conn->affected_rows;
        }

        $response['status'] = "success";
        $response['rows_updated'] = $num_updated;

        return $response;
    }
}
