<?php

namespace classes\access;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\ApiKey;
use classes\Utility;

/**
 * Class to access api user data
 */
class ApiUserAccess
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
     * Methdo to authenticate a request to the API
     * Returns FALSE if not authenticated, otherwise returns user access level
     */
    public function authenticateRequest($api_user_id, $inputs, $sig): array
    {
        // Get user data
        $user_data = $this->userData($api_user_id, true);

        // Exit if invalid user ID
        if ($user_data == null) {
            return Utility::defaultReturn();
        }

        // Remove authentication info from input, to get only request
        unset($inputs['api_user_id']);
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

    /**
     * Method to create a new user, if successful, returns the new user details
     */
    public function createUser($values): array
    {

        $query = "INSERT INTO API_users (id, user_level_id, email, api_key)
                VALUES (NULL, ?, ?, ?);";

        $unique_key = true;
        do {
            $key = ApiKey::generateKey();

            $stmt = $this->conn->prepare($query);
            // Exit + error message on failed connection
            if (!$stmt) {
                return Utility::errorReturn($this->conn->error);
            }

            $stmt->bind_param("iss", $values['level'], $values['email'], $key);
            $stmt->execute();

            // Check if already exists error
            if (mysqli_errno($this->conn) == 1062) {
                $error_msg = $this->conn->error;
                if (Utility::str_contains($error_msg, "email")) {
                    return Utility::errorReturn($this->conn->error);
                } else if (Utility::str_contains($error_msg, "key")) {
                    $unique_key = false;
                }
            }
        } while ($unique_key === false);

        if ($this->conn->affected_rows > 0) {
            $new_user = $this->userData($values['email']);
            return $new_user;
        } else {
            return Utility::defaultReturn();
        }
    }

    public function deleteUser($api_user_id): array
    {
        $query = "DELETE FROM API_users WHERE API_users.id = ?;";

        $stmt = $this->conn->prepare($query);
        // Exit + error message on failed connection
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        $stmt->bind_param("i", $api_user_id);
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
            $query = "SELECT id, user_level_id, email, api_key FROM API_users WHERE {$search} = ?;";
        } else {
            $query = "SELECT API_users.id AS id, SERVICE_user_level.level, email, api_key
                FROM (API_users
                INNER JOIN SERVICE_user_level ON API_users.user_level_id = SERVICE_user_level.id)
                WHERE API_users.{$search} = ?;";
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

    public function updateUserData($api_user_id, $input)
    {   // Would be easier with PDO rather than mysqli
        $array_key = key($input);

        switch ($array_key) {
            case "email":
                $name = "email";
                $type = "si";
                break;
            case "user":
                $name = "user_level_id";
                $type = "ii";
                break;
        }

        $query = "UPDATE API_users
                SET " . $name . " = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        // Exit + error message on failed connection
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        $stmt->bind_param($type, $input[$array_key], $api_user_id);
        $stmt->execute();

        $response['status'] = "success";
        $response['rows_updated'] = $this->conn->affected_rows;

        return $response;
    }
}
