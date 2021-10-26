<?php

namespace classes\access;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to access the app_data table, return data as associative array (where possible)
 */
class AppReviewAccess
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
     * Get reviews from Review table
     */
    public function appReviews($id): array
    {

        $query = "SELECT APP_reviews.id, app_data_id, review, APP_review_sentiment.sentiment, sentiment_polarity, sentiment_subjectivity
        FROM ((APP_reviews
        INNER JOIN APP_data ON APP_reviews.app_data_id = APP_data.id)
        INNER JOIN APP_review_sentiment ON APP_reviews.review_sentiment_id = APP_review_sentiment.id)
        WHERE APP_reviews.app_data_id = ?;";

        $stmt = $this->conn->prepare($query);
        // Exit + error message on failed connection
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $response['status'] = "success";
        $response['data'] = $result->fetch_all(MYSQLI_ASSOC);

        return $response;
    }

    /**
     * Insert a new entry into the app review table
     */
    public function insertAppReview(array $values): array
    {
        $query = "INSERT INTO APP_reviews (id, app_data_id, review, review_sentiment_id, sentiment_polarity, sentiment_subjectivity)
        VALUES (NULL, ?, ?, ?, ?, ?);";

        $stmt = $this->conn->prepare($query);
        // Exit + error message on failed connection
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        // Date input as string, must be format recognisable to sql eg. YYYY-MM-DD
        $stmt->bind_param("isidd", ...$values);
        $stmt->execute();

        if ($this->conn->affected_rows > 0) {
            $response['status'] = "success";
            $response['rows_updated'] = $this->conn->affected_rows;
        } else {
            $response = Utility::defaultReturn();
        }
        return $response;
    }

    /**
     * Delete a review fromt he reviews table
     */
    public function deleteAppReview($id): array
    {
        $query = "DELETE FROM APP_reviews WHERE APP_reviews.id = ?;";

        $stmt = $this->conn->prepare($query);
        // Exit + error message on failed connection
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($this->conn->affected_rows > 0) {
            $response['status'] = "success";
            $response['rows_updated'] = $this->conn->affected_rows;
        } else {
            $response = Utility::defaultReturn();
        }
        return $response;
    }

    /**
     * update a review int he reviews table
     */
    public function updateAppReview($input): array
    {   // Would be easier with PDO rather than mysqli
        $id = $input['target_id'];
        $array_keys = array_keys($input);
        $num_updated = 0;

        for ($loop = 0; $loop < count($input); $loop++) {
            $invalid = false;
            switch ($array_keys[$loop]) {
                case "app_data_id":
                    $name = "app_data_id";
                    $type = "ii";
                    break;
                case "review":
                    $name = "review";
                    $type = "si";
                    break;
                case "review_sentiment_id":
                    $name = "review_sentiment_id";
                    $type = "ii";
                    break;
                case "sentiment_polarity":
                    $name = "sentiment_polarity";
                    $type = "di";
                    break;
                case "sentiment_subjectivity":
                    $name = "sentiment_subjectivity";
                    $type = "di";
                    break;
                default:
                    $invalid = true;
                    break;
            }

            // If an input invalid, skip that iteration 
            if ($invalid == true) {
                continue;
            }

            $query = "UPDATE APP_reviews
                SET " . $name . " = ?
                WHERE id = ?";

            $stmt = $this->conn->prepare($query);
            // Exit + error message on failed connection
            if (!$stmt) {
                return Utility::errorReturn($this->conn->error);
            }

            $stmt->bind_param($type, $input[$array_keys[$loop]], $id);
            $stmt->execute();

            $num_updated += $this->conn->affected_rows;
        }
        if ($num_updated > 0) {
            $response['status'] = "success";
            $response['rows_updated'] = $num_updated;
        } else {
            $response['status'] = "fail";
            $response['rows_updated'] = $num_updated;
        }

        return $response;
    }
} // END