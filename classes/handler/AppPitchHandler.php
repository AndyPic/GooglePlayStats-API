<?php

namespace classes\handler;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/connection.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to handle app Pitch requests 
 */
class AppPitchHandler
{
    // Instance Vars

    private $accessAppPitch;
    private $conn;

    // Constructors

    /**
     * Constructor with args
     */
    public function __construct($conn)
    {
        $this->accessAppPitch = new \classes\access\AppPitchAccess($conn);
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

                if (isset($_GET['pitch'])) {
                    if (isset($_GET['page'], $_GET['pp'])) {
                        $return = $this->getPitchData($_GET['pitch'], $_GET['page'], $_GET['pp']);
                    } else {
                        $return = $this->getPitchData($_GET['pitch']);
                    }
                }

                if (isset($_GET["search"]) || isset($_GET["filter_key"]) || isset($_GET["filter_value"]) || isset($_GET["order"])) {
                    @$return = $this->runAdvancedSearch($_GET["search"], $_GET["filter_key"], $_GET["filter_value"], $_GET["order"], $_GET['page'], $_GET['pp']);
                }

                break;

            case "POST": // INSERT requests

                if (
                    isset(
                        $_POST['app_user_id'],
                        $_POST['pitch_title'],
                        $_POST['name'],
                        $_POST['genre_id'],
                        $_POST['description'],
                        $_POST['motive'],
                        $_POST['audience']
                    )
                ) {
                    $values = [
                        'app_user_id' => $_POST['app_user_id'],
                        'pitch_title' => $_POST['pitch_title'],
                        'name' => $_POST['name'],
                        'genre_id' => $_POST['genre_id'],
                        'description' => $_POST['description'],
                        'motive' => $_POST['motive'],
                        'audience' => $_POST['audience']
                    ];

                    $return = $this->runCreatePitch($values);
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
                        isset($put_vars['pitch_title']) || isset($put_vars['name']) || isset($put_vars['genre_id']) || isset($put_vars['description']) || isset($put_vars['motive'])
                        || isset($put_vars['audience']) || isset($put_vars['rating'])
                    ) {
                        $return = $this->runUpdatePitch($put_vars);
                    }
                }
                break;
            case "DELETE": // DELETE requests

                if ($auth < 4) {
                    $return = json_encode(Utility::invalidAuthReturn());
                    break;
                }

                // Get DELETE data in to associative array
                parse_str(file_get_contents("php://input"), $del_vars);

                if (isset($del_vars['delete_pitch']) && $del_vars['delete_pitch'] != "") {
                    $id = $del_vars['delete_pitch'];
                    $return = $this->runDeletePitch($id);
                }
                break;
            default:
                $return = json_encode(Utility::defaultReturn());
                break;
        }
        echo $return;
    }

    private function getPitchData(...$inputs)
    {
        $response = $this->accessAppPitch->pitchData(...$inputs);
        return json_encode($response);
    }

    private function runAdvancedSearch(...$inputs)
    {
        $response = $this->accessAppPitch->advancedSearch(...$inputs);
        return json_encode($response);
    }

    private function runCreatePitch($values)
    {
        $response = $this->accessAppPitch->createPitch($values);
        return json_encode($response);
    }

    private function runUpdatePitch($values)
    {
        $response = $this->accessAppPitch->updatePitch($values);
        return json_encode($response);
    }

    private function runDeletePitch($id)
    {
        $response = $this->accessAppPitch->deletePitch($id);
        return json_encode($response);
    }
}
