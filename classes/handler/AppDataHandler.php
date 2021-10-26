<?php

namespace classes\handler;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/connection.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to handle api requests 
 */
class AppDataHandler
{

    // Instance Vars

    private $accessAppData;
    private $conn;

    // Constructors

    /**
     * Constructor with args
     */
    public function __construct($conn)
    {
        $this->accessAppData = new \classes\access\AppDataAccess($conn);
        $this->conn = $conn;
    }

    // Methods

    /**
     * Handle requests and return JSON where applicable
     */
    public function requestHandler($auth, $method)
    {
        // Default return
        $return = json_encode(Utility::defaultReturn());

        // Check request method
        switch ($method) {
            case "GET": // SELECT requests

                if (isset($_GET['count'])) {
                    if ($_GET['count'] != "") {
                        $return = $this->runCountApps($_GET['count']);
                    } else {
                        $return = $this->runCountApps();
                    }
                }

                if (isset($_GET["data"])) {
                    if (isset($_GET['page'], $_GET['pp'])) {
                        $return = $this->runAppData($_GET["data"], $_GET['page'], $_GET['pp']);
                    } else {
                        $return = $this->runAppData($_GET["data"]);
                    }
                }

                if (isset($_GET["app_names"])) {
                    $return = $this->runAppNames();
                }

                if (isset($_GET["search"]) || isset($_GET["filter_key"]) || isset($_GET["filter_value"]) || isset($_GET["order"])) {
                    @$return = $this->runAdvancedSearch($_GET["search"], $_GET["filter_key"], $_GET["filter_value"], $_GET["order"], $_GET['page'], $_GET['pp']);
                }
                break;

            case "POST": // INSERT requests

                if ($auth < 3) {
                    return json_encode(Utility::invalidAuthReturn());
                }

                if (
                    isset(
                        $_POST['name'],
                        $_POST['rating'],
                        $_POST['number_of_reviews'],
                        $_POST['size'],
                        $_POST['number_of_installs_id'],
                        $_POST['price'],
                        $_POST['content_rating_id'],
                        $_POST['genre_id'],
                        $_POST['date_updated'],
                        $_POST['current_version'],
                        $_POST['android_os_support'],
                        $_POST['img_url'],
                        $_POST['description'],
                        $_POST['google_app_url'],
                        $_POST['developer_id']
                    )
                ) {
                    $values = [
                        'name' => $_POST['name'],
                        'rating' => $_POST['rating'],
                        'number_of_reviews' => $_POST['number_of_reviews'],
                        'size' => $_POST['size'],
                        'number_of_installs_id' => $_POST['number_of_installs_id'],
                        'price' => $_POST['price'],
                        'content_rating_id' => $_POST['content_rating_id'],
                        'genre_id' => $_POST['genre_id'],
                        'date_updated' => $_POST['date_updated'],
                        'current_version' => $_POST['current_version'],
                        'android_os_support' => $_POST['android_os_support'],
                        'img_url' => $_POST['img_url'],
                        'description' => $_POST['description'],
                        'google_app_url' => $_POST['google_app_url'],
                        'developer_id' => $_POST['developer_id']
                    ];

                    $return = $this->runInsertAppData($values);
                }
                break;
            case "PUT": // UPDATE requests

                if ($auth < 3) {
                    return json_encode(Utility::invalidAuthReturn());
                }

                // Get PUT data in to associative array
                parse_str(file_get_contents("php://input"), $put_vars);

                if (isset($put_vars['target_id'])) {
                    if (
                        isset($put_vars['name']) || isset($put_vars['rating']) || isset($put_vars['number_of_reviews']) || isset($put_vars['size']) || isset($put_vars['number_of_installs_id'])
                        || isset($put_vars['price']) || isset($put_vars['content_rating_id']) || isset($put_vars['genre_id']) || isset($put_vars['date_updated']) || isset($put_vars['current_version'])
                        || isset($put_vars['android_os_support']) || isset($put_vars['img_url']) || isset($put_vars['description']) || isset($put_vars['google_app_url']) || isset($put_vars['developer_id'])
                    ) {
                        $return = $this->runUpdateAppData($put_vars);
                    }
                }
                break;
            case "DELETE": // DELETE requests

                if ($auth < 4) {
                    return json_encode(Utility::invalidAuthReturn());
                }

                // Get DELETE data in to associative array
                parse_str(file_get_contents("php://input"), $del_vars);

                if (isset($del_vars['delete_app'])) {
                    $id = $del_vars['delete_app'];
                    $return = $this->runDeleteAppData($id);
                }
                break;
            default:
                $return = json_encode(Utility::defaultReturn());
                break;
        }
        echo $return;
    }

    private function runAdvancedSearch(...$inputs)
    {
        $return = $this->accessAppData->advancedSearch(...$inputs);
        return json_encode($return);
    }

    private function runDeleteAppData($id)
    {
        $return = $this->accessAppData->deleteAppData($id);
        return json_encode($return);
    }

    private function runAppNames()
    {
        $return = $this->accessAppData->appNames();
        return json_encode($return);
    }

    private function runCountApps($values = null)
    {
        $count = $this->accessAppData->countApps($values);
        return json_encode($count);
    }

    private function runInsertAppData($values)
    {
        $return = $this->accessAppData->insertAppData($values);
        return json_encode($return);
    }

    private function runAppData($data, ...$values)
    {
        $data = $this->accessAppData->appData($data, ...$values);
        return json_encode($data);
    }

    private function runUpdateAppData($update)
    {
        $updated = $this->accessAppData->updateAppData($update);
        return json_encode($updated);
    }
} // END