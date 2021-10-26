<?php

namespace classes\access;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to access the app_data table, return data as associative array (where possible)
 */
class AppDataAccess
{
    // Instance Vars
    private $conn;
    private $countBaseQuery = "SELECT COUNT(*) AS 'count' FROM APP_data";
    private $queryAllJoins = "SELECT APP_data.id, name, APP_genre.genre, APP_number_of_installs.number AS 'number_of_installs', APP_content_rating.rating AS 'content_rating', APP_developers.developer 
    AS developer, APP_data.rating, number_of_reviews, size, price, date_updated, current_version, android_os_support, img_url, google_app_url, description 
    FROM ((((APP_data 
    INNER JOIN APP_number_of_installs ON APP_data.number_of_installs_id = APP_number_of_installs.id)
    INNER JOIN APP_content_rating ON APP_data.content_rating_id = APP_content_rating.id)
    INNER JOIN APP_genre ON APP_data.genre_id = APP_genre.id) 
    INNER JOIN APP_developers ON APP_data.developer_id = APP_developers.id)";

    // Constructors

    /**
     * Constructor with arg
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Methods


    // Count all apps
    public function countApps()
    {
        $response = array();
        $params = array();
        $types = "";

        $response = Utility::fetchResponse($this->conn, $this->countBaseQuery, @$params, @$types);
        return $response;
    }

    // Get all app data
    public function appData($data = null, $page = null, $pp = null): array
    {
        $query = $this->queryAllJoins;
        $response = array();
        $params = array();
        $types = "";

        $response['count'] = $this->countApps()['data'][0]['count'];

        if ($page != null && $pp != null) {
            $query .= " ORDER BY APP_data.name LIMIT ?, ? ";
            $response['pages'] = ceil($response['count'] / $pp);
            $start_point = (($pp * $page) - $pp);
            $types .= "ii";
            array_push($params, $start_point, $pp);
        } else if ($data != null) {
            $query .= " WHERE APP_data.id = ?";
            $types .= "i";
            array_push($params, $data);
        } else {
            $query .= " ORDER BY APP_data.name";
        }


        $response = array_merge($response, Utility::fetchResponse($this->conn, $query, @$params, @$types));
        return $response;
    }

    /**
     * Returns all app names within DB
     */
    public function appNames(): array
    {
        $data['query'] = "SELECT id, name FROM APP_data";
        $data['response']['count'] = $this->countApps()['data'][0]['count'];

        $data['response'] = array_merge($data['response'], Utility::fetchResponse($this->conn, $data['query']));
        return $data['response'];
    }

    public function advancedSearch($search = null, $filterKey = null, $filterValue = null, $order = null, $page = null, $pp = null): array
    {
        $query = "";
        $types = "";

        $countQuery = "SELECT COUNT(*) AS 'count' FROM APP_data";

        $params = array();

        // Get search word
        if ($search !== null) {
            $search = "%{$search}%";
            $params[] = $search;
            $query .= " WHERE `name` LIKE ?";
            $types .= "s";
        }

        // Get filter
        if ($filterKey !== null && $filterValue !== null) {
            if ($search !== null) {
                $query .= " AND";
            }
            switch ($filterKey) {
                case "genre":
                    $query .= " `genre_id` = ?";
                    break;
                case "installs":
                    $query .= " `number_of_installs_id` = ?";
                    break;
                case "content_rating":
                    $query .= " `content_rating_id` = ?";
                    break;
                case "developer":
                    $query .= " `developer_id` = ?";
                    break;
            }
            $types .= "i";
            $params[] = $filterValue;
        }

        // Get order
        if ($order !== null) {
            switch ($order) {
                case "name":
                    $query .= " ORDER BY `name`";
                    break;
                case "reviews":
                    $query .= " ORDER BY `number_of_reviews`";
                    break;
                case "rating":
                    $query .= " ORDER BY `rating`";
                    break;
                case "price":
                    $query .= " ORDER BY `price`";
                    break;
                case "updated":
                    $query .= " ORDER BY `date_updated`";
                    break;
            }
        } else {
            // Default order by name
            $query .= " ORDER BY `name`";
        }

        // Count returns
        $countQuery .= $query;
        $response['count'] = Utility::fetchResponse($this->conn, $countQuery, @$params, @$types)['data'][0]['count'];

        // Paginate
        if ($page !== null && $pp !== null) {
            $query .= " LIMIT ?, ?";
            $response['pages'] = ceil($response['count'] / $pp);
            $start_point = (($pp * $page) - $pp);
            $types .= "ii";
            array_push($params, $start_point, $pp);
        }

        $query = $this->queryAllJoins . $query;

        // Return
        $response = array_merge($response, Utility::fetchResponse($this->conn, $query, @$params, @$types));
        return $response;
    }

    /**
     * Insert a new entry into the app data table
     */
    public function insertAppData(array $values): array
    {
        $query = "INSERT INTO APP_data (id, name, genre_id, number_of_installs_id, content_rating_id, developer_id, rating, 
        number_of_reviews, size, price, date_updated, current_version, android_os_support, img_url, google_app_url, description)
        VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        // Date input as string, must be format recognisable to sql eg. YYYY-MM-DD
        $stmt->bind_param(
            "sdisidiisssssss",
            $values['name'],
            $values['rating'],
            $values['number_of_reviews'],
            $values['size'],
            $values['number_of_installs_id'],
            $values['price'],
            $values['content_rating_id'],
            $values['genre_id'],
            $values['date_updated'],
            $values['current_version'],
            $values['android_os_support'],
            $values['img_url'],
            $values['description'],
            $values['google_app_url'],
            $values['developer_id']
        );
        $stmt->execute();

        // Check if app already exists error
        if (mysqli_errno($this->conn) == 1062 && str_contains($this->conn->error, "name")) {
            return Utility::errorReturn($this->conn->error);
        }

        if ($this->conn->affected_rows > 0) {
            $response['status'] = "success";
        } else {
            $response['status'] = "fail";
        }
        return $response;
    }

    public function deleteAppData($id): array
    {
        $query = "DELETE FROM APP_data WHERE APP_data.id = ?;";

        $stmt = $this->conn->prepare($query);
        // Exit + error message on failed connection
        if (!$stmt) {
            return Utility::errorReturn($this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($this->conn->affected_rows > 0) {
            $response['status'] = "success";
        } else {
            $response['status'] = "fail";
        }
        return $response;
    }

    public function updateAppData($update): array
    {
        $id = $update['target_id'];
        $num_updated = 0;
        $array_keys = array_keys($update);

        for ($loop = 0; $loop < count($update); $loop++) {

            $invalid = false;

            switch ($array_keys[$loop]) {
                case "name":
                    $name = "name";
                    $type = "si";
                    break;
                case "rating":
                    $name = "rating";
                    $type = "di";
                    break;
                case "number_of_reviews":
                    $name = "number_of_reviews";
                    $type = "ii";
                    break;
                case "size":
                    $name = "size";
                    $type = "si";
                    break;
                case "number_of_installs_id":
                    $name = "number_of_installs_id";
                    $type = "ii";
                    break;
                case "price":
                    $name = "price";
                    $type = "di";
                    break;
                case "content_rating_id":
                    $name = "content_rating_id";
                    $type = "ii";
                    break;
                case "genre_id":
                    $name = "genre_id";
                    $type = "ii";
                    break;
                case "date_updated":
                    $name = "date_updated";
                    $type = "si";
                    break;
                case "current_version":
                    $name = "current_version";
                    $type = "si";
                    break;
                case "android_os_support":
                    $name = "android_os_support";
                    $type = "si";
                    break;
                case "img_url":
                    $name = "img_url";
                    $type = "si";
                    break;
                case "description":
                    $name = "description";
                    $type = "si";
                    break;
                case "google_app_url":
                    $name = "google_app_url";
                    $type = "si";
                    break;
                case "developer_id":
                    $name = "developer_id";
                    $type = "si";
                    break;
                default:
                    $invalid = true;
                    break;
            }

            // If an input invalid, skip that iteration 
            if ($invalid == true) {
                continue;
            }

            $query = "UPDATE APP_data
                SET " . $name . " = ?
                WHERE id = ?";

            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return Utility::errorReturn($this->conn->error);
            }

            $stmt->bind_param($type, $update[$array_keys[$loop]], $id);
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
} //END