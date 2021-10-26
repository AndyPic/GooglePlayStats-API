<?php

namespace classes\handler;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/connection.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to handle app year requests 
 */
class AppYearHandler
{
    // Instance Vars

    private $accessAppYear;
    private $conn;

    // Constructors

    /**
     * Constructor with args
     */
    public function __construct($conn)
    {
        $this->accessAppYear = new \classes\access\AppYearAccess($conn);
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
                if (isset($_GET['reviews']) && $_GET['reviews'] == "") {
                    $return = $this->getYearReviews();
                } else if (isset($_GET['reviews']) && $_GET['reviews'] != "") {
                    $return = $this->getYearReviewsSingle($_GET['reviews']);
                }

                if (isset($_GET['revenue']) && $_GET['revenue'] == "") {
                    $return = $this->getYearRevenue();
                } else if (isset($_GET['revenue']) && $_GET['revenue'] != "") {
                    $return = $this->getYearRevenueSingle($_GET['revenue']);
                }

                if (isset($_GET['rating']) && $_GET['rating'] == "") {
                    $return = $this->getYearRating();
                } else if (isset($_GET['rating']) && $_GET['rating'] != "") {
                    $return = $this->getYearRatingSingle($_GET['rating']);
                }

                if (isset($_GET['installs']) && $_GET['installs'] == "") {
                    $return = $this->getYearInstalls();
                } else if (isset($_GET['installs']) && $_GET['installs'] != "") {
                    $return = $this->getYearInstallsSingle($_GET['installs']);
                }
                break;
        }
        echo $return;
    }

    private function getYearReviews()
    {
        $response = $this->accessAppYear->yearReviews();
        return json_encode($response);
    }

    private function getYearReviewsSingle($param)
    {
        if (is_numeric($param) && $param > 0) {
            $response = $this->accessAppYear->yearReviewsSingle($param);
        } else {
            $response = Utility::defaultReturn();
        }
        return json_encode($response);
    }

    private function getYearRevenue()
    {
        $response = $this->accessAppYear->yearRevenue();
        return json_encode($response);
    }

    private function getYearRevenueSingle($param)
    {
        if (is_numeric($param) && $param > 0) {
            $response = $this->accessAppYear->yearRevenueSingle($param);
        } else {
            $response = Utility::defaultReturn();
        }
        return json_encode($response);
    }

    private function getYearRating()
    {
        $response = $this->accessAppYear->yearRating();
        return json_encode($response);
    }

    private function getYearRatingSingle($param)
    {
        if (is_numeric($param) && $param > 0) {
            $response = $this->accessAppYear->yearRatingSingle($param);
        } else {
            $response = Utility::defaultReturn();
        }
        return json_encode($response);
    }

    private function getYearInstalls()
    {
        $response = $this->accessAppYear->yearInstalls();
        return json_encode($response);
    }

    private function getYearInstallsSingle($param)
    {
        if (is_numeric($param) && $param > 0) {
            $response = $this->accessAppYear->yearInstallsSingle($param);
        } else {
            $response = Utility::defaultReturn();
        }
        return json_encode($response);
    }
}
