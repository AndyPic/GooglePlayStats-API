<?php

namespace classes\handler;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/connection.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to handle app review requests 
 */
class AppReviewHandler
{
    // Instance Vars

    private $accessAppReview;
    private $conn;

    // Constructors

    /**
     * Constructor with args
     */
    public function __construct($conn)
    {
        $this->accessAppReview = new \classes\access\AppReviewAccess($conn);
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

                if (isset($_GET['review']) && $_GET['review'] != "") {
                    $return = $this->runAppReviews($_GET['review']);
                }
                break;

            case "POST": // INSERT requests

                if ($auth < 3) {
                    return json_encode(Utility::invalidAuthReturn());
                }

                if (
                    isset($_POST['app_data_id']) && isset($_POST['review']) && isset($_POST['review_sentiment_id'])
                    && isset($_POST['sentiment_polarity']) && isset($_POST['sentiment_subjectivity'])
                ) {
                    $values = [
                        "app_data_id" => $_POST['app_data_id'],
                        "review" => $_POST['review'],
                        "review_sentiment_id" => $_POST['review_sentiment_id'],
                        "sentiment_polarity" => $_POST['sentiment_polarity'],
                        "sentiment_subjectivity" => $_POST['sentiment_subjectivity']
                    ];
                    $return = $this->runInsertAppReview($values);
                }
                break;

            case "PUT": // UPDATE requests

                if ($auth < 4) {
                    return json_encode(Utility::invalidAuthReturn());
                }

                // Get PUT data in to associative array
                parse_str(file_get_contents("php://input"), $put_vars);

                if (isset($put_vars['target_id'])) {
                    if (
                        isset($put_vars['app_data_id']) || isset($put_vars['review']) || isset($put_vars['review_sentiment_id'])
                        || isset($put_vars['sentiment_polarity']) || isset($put_vars['sentiment_subjectivity'])
                    ) {
                        $return = $this->runUpdateAppReview($put_vars);
                    }
                }

                break;

            case "DELETE": // DELETE requests

                if ($auth < 4) {
                    return json_encode(Utility::invalidAuthReturn());
                }

                // Get DELETE data in to associative array
                parse_str(file_get_contents("php://input"), $del_vars);

                if (isset($del_vars['delete_review'])) {
                    $return = $this->runDeleteAppReview($del_vars['delete_review']);
                }
                break;
        }
        echo $return;
    }

    private function runAppReviews($id)
    {
        $reviews = $this->accessAppReview->appReviews($id);
        return json_encode($reviews);
    }

    private function runInsertAppReview(array $values)
    {
        $return = $this->accessAppReview->insertAppReview($values);
        return json_encode($return);
    }

    private function runUpdateAppReview($input)
    {
        $return = $this->accessAppReview->updateAppReview($input);
        return json_encode($return);
    }

    private function runDeleteAppReview($id)
    {
        $return = $this->accessAppReview->deleteAppReview($id);
        return json_encode($return);
    }
}// END