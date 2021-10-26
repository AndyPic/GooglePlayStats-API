<?php

namespace classes;

/**
 * Collection of static utility functions
 */
class Utility
{
    // Insatnce Vars

    // Constructors

    /**
     * Default constructor
     */
    public function __construct()
    {
    }

    // Methods

    /**
     * Check if a string is contained within another string.
     * 
     * PHP 8.0+ has inbuild, would beed removing!
     */
    public static function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }

    /**
     * Fucntion to convert a base 10 number
     * 
     * From stack overflow
     * https://stackoverflow.com/questions/4964197/converting-a-number-base-10-to-base-62-a-za-z0-9
     */
    public static function toBase($num, $b = 62): string
    {
        $base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $r = $num  % $b;
        $res = $base[$r];
        $q = floor($num / $b);
        while ($q) {
            $r = $q % $b;
            $q = floor($q / $b);
            $res = $base[$r] . $res;
        }
        return $res;
    }

    /**
     * Fucntion to convert (back) to base 10
     * 
     * From stack overflow
     * https://stackoverflow.com/questions/4964197/converting-a-number-base-10-to-base-62-a-za-z0-9
     */
    public static function to10($num, $b = 62): string
    {
        $base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $limit = strlen($num);
        $res = strpos($base, $num[0]);
        for ($i = 1; $i < $limit; $i++) {
            $res = $b * $res + strpos($base, $num[$i]);
        }
        return $res;
    }

    /**
     * Default return for invalid API request
     */
    public static function defaultReturn()
    {
        $response['status'] = "fail";
        $response['fail_msg'] = "invalid parameters";
        $response['data'] = null;

        return $response;
    }

    /**
     * Invalid authentication response
     */
    public static function invalidAuthReturn()
    {
        $response['status'] = "fail";
        $response['fail_msg'] = "invalid authentication";
        $response['data'] = null;

        return $response;
    }

    /**
     * Error response
     */
    public static function errorReturn($e)
    {
        $response['status'] = "error";
        $response['error_msg'] = $e;
        $response['data'] = null;

        return $response;
    }

    public static function checkType($input)
    {
        switch (gettype($input)) {
            case "integer":
                return "i";
                break;
            case "double":
                return "d";
                break;
            case "string":
                return "s";
                break;
            default:
                return null;
        }
    }

    /**
     * Private function to fetch response (assoc array) from DB, from input details
     * Passed params MUST be an array
     */
    public static function fetchResponse($conn, $query, $params = null, $types = null)
    {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return Utility::errorReturn($conn->error);
        }

        // Bind params, if defined
        if ($params != null && $types != null) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $updated_id = $stmt->insert_id;
        $result = $stmt->get_result();
        if (!$result && $updated_id == 0) {
            return Utility::errorReturn($conn->error);
        }
        $response['status'] = "success";

        if ($result != false) {
            $response['data'] = $result->fetch_all(MYSQLI_ASSOC);
        }
        
        if ($updated_id != 0) {
            $response['id'] = $updated_id;
        }

        return $response;
    }
} // END
