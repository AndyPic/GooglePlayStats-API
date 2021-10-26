<?php

namespace classes\handler;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/connection.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to handle app service requests 
 */
class AppServiceHandler
{
    // Instance Vars

    private $accessAppService;
    private $conn;

    // Constructors

    /**
     * Constructor with args
     */
    public function __construct($conn)
    {
        $this->accessAppService = new \classes\access\AppServiceAccess($conn);
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

                if (isset($_GET['content_rating'])) {
                    $return = $this->getContentRating();
                }

                if (isset($_GET['developers'])) {
                    $return = $this->getDevelopers();
                }

                if (isset($_GET['genre'])) {
                    $return = $this->getGenre();
                }

                if (isset($_GET['installs'])) {
                    $return = $this->getNumberOfInstalls();
                }
                break;
        }
        echo $return;
    }

    private function getContentRating()
    {
        $response = $this->accessAppService->contentRating();
        return json_encode($response);
    }

    private function getDevelopers()
    {
        $response = $this->accessAppService->developers();
        return json_encode($response);
    }

    private function getGenre()
    {
        $response = $this->accessAppService->genre();
        return json_encode($response);
    }
    private function getNumberOfInstalls()
    {
        $response = $this->accessAppService->numberOfInstalls();
        return json_encode($response);
    }
}
