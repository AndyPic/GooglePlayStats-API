<?php

namespace classes\access;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to access service tables
 */
class AppServiceAccess
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
     * Get content rating options
     */
    public function contentRating()
    {
        $query = "SELECT `id`, `rating` FROM `APP_content_rating`";

        $response = Utility::fetchResponse($this->conn, $query);
        return $response;
    }

    /**
     * Get content rating options
     */
    public function developers()
    {
        $query = "SELECT `id`, `developer` FROM `APP_developers`";

        $response = Utility::fetchResponse($this->conn, $query);
        return $response;
    }

    /**
     * Get content rating options
     */
    public function genre()
    {
        $query = "SELECT `id`, `genre` FROM `APP_genre`";

        $response = Utility::fetchResponse($this->conn, $query);
        return $response;
    }

    /**
     * Get content rating options
     */
    public function numberOfInstalls()
    {
        $query = "SELECT `id`, `number` FROM `APP_number_of_installs`";

        $response = Utility::fetchResponse($this->conn, $query);
        return $response;
    }
}
