<?php

namespace classes\access;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to access the year data tables
 */
class AppYearAccess
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
     * Get year review data for all apps
     */
    public function yearReviews()
    {
        $query = "SELECT APP_year_reviews.id, `data_id`, APP_data.name AS name, `month_01`, `month_02`, `month_03`, `month_04`, 
        `month_05`, `month_06`, `month_07`, `month_08`, `month_09`, `month_10`, `month_11`, `month_12` 
        FROM (`APP_year_reviews`
              INNER JOIN APP_data ON APP_data.id = APP_year_reviews.data_id)";

        $response = Utility::fetchResponse($this->conn, $query);
        return $response;
    }

    /**
     * Get year review data for a single app
     */
    public function yearReviewsSingle($param)
    {
        $query = "SELECT APP_year_reviews.id, `data_id`, APP_data.name AS name, `month_01`, `month_02`, `month_03`, `month_04`, 
        `month_05`, `month_06`, `month_07`, `month_08`, `month_09`, `month_10`, `month_11`, `month_12` 
        FROM (`APP_year_reviews`
              INNER JOIN APP_data ON APP_data.id = APP_year_reviews.data_id)
        WHERE data_id = ?";

        $params[] = $param;

        $response = Utility::fetchResponse($this->conn, $query, $params, "i");
        return $response;
    }

    /**
     * Get year review data for all apps
     */
    public function yearRevenue()
    {
        $query = "SELECT APP_year_revenue.id, `data_id`, APP_data.name AS name, `month_01`, `month_02`, `month_03`, `month_04`, 
        `month_05`, `month_06`, `month_07`, `month_08`, `month_09`, `month_10`, `month_11`, `month_12` 
        FROM (`APP_year_revenue`
              INNER JOIN APP_data ON APP_data.id = APP_year_revenue.data_id)";

        $response = Utility::fetchResponse($this->conn, $query);
        return $response;
    }

    /**
     * Get year review data for a single app
     */
    public function yearRevenueSingle($param)
    {
        $query = "SELECT APP_year_revenue.id, `data_id`, APP_data.name AS name, `month_01`, `month_02`, `month_03`, `month_04`, 
        `month_05`, `month_06`, `month_07`, `month_08`, `month_09`, `month_10`, `month_11`, `month_12` 
        FROM (`APP_year_revenue`
              INNER JOIN APP_data ON APP_data.id = APP_year_revenue.data_id)
        WHERE data_id = ?";

        $params[] = $param;

        $response = Utility::fetchResponse($this->conn, $query, $params, "i");
        return $response;
    }

    /**
     * Get year review data for all apps
     */
    public function yearRating()
    {
        $query = "SELECT APP_year_rating.id, `data_id`, APP_data.name AS name, `month_01`, `month_02`, `month_03`, `month_04`, 
        `month_05`, `month_06`, `month_07`, `month_08`, `month_09`, `month_10`, `month_11`, `month_12` 
        FROM (`APP_year_rating`
              INNER JOIN APP_data ON APP_data.id = APP_year_rating.data_id)";

        $response = Utility::fetchResponse($this->conn, $query);
        return $response;
    }

    /**
     * Get year review data for a single app
     */
    public function yearRatingSingle($param)
    {
        $query = "SELECT APP_year_rating.id, `data_id`, APP_data.name AS name, `month_01`, `month_02`, `month_03`, `month_04`, 
        `month_05`, `month_06`, `month_07`, `month_08`, `month_09`, `month_10`, `month_11`, `month_12` 
        FROM (`APP_year_rating`
              INNER JOIN APP_data ON APP_data.id = APP_year_rating.data_id)
        WHERE data_id = ?";

        $params[] = $param;

        $response = Utility::fetchResponse($this->conn, $query, $params, "i");
        return $response;
    }

    /**
     * Get year review data for all apps
     */
    public function yearInstalls()
    {
        $query = "SELECT year.id,  year.data_id, APP_data.name AS name, numOne.number AS month_01, numTwo.number AS month_02,
        numThree.number AS month_03, numFour.number AS month_04,
        numFive.number AS month_05, numSix.number AS month_06,
        numSeven.number AS month_07, numEight.number AS month_08,
        numNine.number AS month_09, numTen.number AS month_10,
        numEleven.number AS month_11, numTwelve.number AS month_12
        FROM APP_year_installs AS year
        JOIN APP_data ON APP_data.id = year.data_id
        JOIN APP_number_of_installs AS numOne ON numOne.id = year.month_01
        JOIN APP_number_of_installs AS numTwo ON numTwo.id = year.month_02
        JOIN APP_number_of_installs AS numThree ON numThree.id = year.month_03
        JOIN APP_number_of_installs AS numFour ON numFour.id = year.month_04
        JOIN APP_number_of_installs AS numFive ON numFive.id = year.month_05
        JOIN APP_number_of_installs AS numSix ON numSix.id = year.month_06
        JOIN APP_number_of_installs AS numSeven ON numSeven.id = year.month_07
        JOIN APP_number_of_installs AS numEight ON numEight.id = year.month_08
        JOIN APP_number_of_installs AS numNine ON numNine.id = year.month_09
        JOIN APP_number_of_installs AS numTen ON numTen.id = year.month_10
        JOIN APP_number_of_installs AS numEleven ON numEleven.id = year.month_11
        JOIN APP_number_of_installs AS numTwelve ON numTwelve.id = year.month_12
        ORDER BY year.id";

        $response = Utility::fetchResponse($this->conn, $query);
        return $response;
    }

    /**
     * Get year review data for a single app
     */
    public function yearInstallsSingle($param)
    {
        $query = "SELECT year.id,  year.data_id, APP_data.name AS name, numOne.number AS month_01, numTwo.number AS month_02,
        numThree.number AS month_03, numFour.number AS month_04,
        numFive.number AS month_05, numSix.number AS month_06,
        numSeven.number AS month_07, numEight.number AS month_08,
        numNine.number AS month_09, numTen.number AS month_10,
        numEleven.number AS month_11, numTwelve.number AS month_12
        FROM APP_year_installs AS year
        JOIN APP_data ON APP_data.id = year.data_id
        JOIN APP_number_of_installs AS numOne ON numOne.id = year.month_01
        JOIN APP_number_of_installs AS numTwo ON numTwo.id = year.month_02
        JOIN APP_number_of_installs AS numThree ON numThree.id = year.month_03
        JOIN APP_number_of_installs AS numFour ON numFour.id = year.month_04
        JOIN APP_number_of_installs AS numFive ON numFive.id = year.month_05
        JOIN APP_number_of_installs AS numSix ON numSix.id = year.month_06
        JOIN APP_number_of_installs AS numSeven ON numSeven.id = year.month_07
        JOIN APP_number_of_installs AS numEight ON numEight.id = year.month_08
        JOIN APP_number_of_installs AS numNine ON numNine.id = year.month_09
        JOIN APP_number_of_installs AS numTen ON numTen.id = year.month_10
        JOIN APP_number_of_installs AS numEleven ON numEleven.id = year.month_11
        JOIN APP_number_of_installs AS numTwelve ON numTwelve.id = year.month_12 
        WHERE data_id = ?";

        $params[] = $param;

        $response = Utility::fetchResponse($this->conn, $query, $params, "i");
        return $response;
    }
}
