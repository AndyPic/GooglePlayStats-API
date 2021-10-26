<?php

namespace classes\access;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');

use classes\Utility;

/**
 * Class to access pitch tables
 */
class AppPitchAccess
{

    // Instance Vars
    private $conn;
    private $countBaseQuery = "SELECT COUNT(*) AS 'count' FROM APP_pitch";
    private $queryAllJoins = "SELECT APP_pitch.id, `user_id`, `pitch_title`, `name`, APP_genre.genre, `description`, `motive`, `audience`, `rating`, `votes` 
    FROM (`APP_pitch`
    INNER JOIN APP_genre ON APP_pitch.genre_id = APP_genre.id)";

    // Constructors

    /**
     * Constructor with arg
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Methods

    // Get all pitch data, or a specific one
    public function pitchData($pitch_id = null, $page = null, $pp = null)
    {
        $query = $this->queryAllJoins;

        $response = array();

        if ($pitch_id != "" && $pitch_id !== null) {
            $query .= " WHERE APP_pitch.id = ?";
            $params[] = $pitch_id;
            $types = "i";
        } else {
            $response['count'] = Utility::fetchResponse($this->conn, $this->countBaseQuery)['data'][0]['count'];

            $query .= " ORDER BY name";

            // Paginate
            if ($page !== null && $pp !== null) {
                $query .= " LIMIT ?, ?";
                $response['pages'] = ceil($response['count'] / $pp);
                $start_point = (($pp * $page) - $pp);
                $types = "ii";
                $params[] = $start_point;
                $params[] = $pp;
            }
        }

        $response = array_merge($response, Utility::fetchResponse($this->conn, $query, @$params, @$types));
        return $response;
    }

    // Get filtered pitch data eg. for specific user or genre
    public function advancedSearch($search = null, $filterKey = null, $filterValue = null, $order = null, $page = null, $pp = null): array
    {
        $query = "";
        $types = "";
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
                case "user":
                    $query .= " `user_id` = ?";
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
                case "votes":
                    $query .= " ORDER BY `votes`";
                    break;
                case "rating":
                    $query .= " ORDER BY `rating`";
                    break;
            }
        } else {
            // Default order by name
            $query .= " ORDER BY `name`";
        }

        // Count returns
        $countQuery = $this->countBaseQuery . $query;
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

    // Get all pitches belonging to a specific user
    public function createPitch($values)
    {
        $query = "INSERT INTO `APP_pitch`(`id`, `user_id`, `pitch_title`, `name`, `genre_id`, `description`, `motive`, `audience`, `rating`, `votes`) 
        VALUES (null,?,?,?,?,?,?,?,0,0);";

        $types = "ississs";
        $params = array(
            $values['user_id'],
            $values['pitch_title'],
            $values['name'],
            $values['genre_id'],
            $values['description'],
            $values['motive'],
            $values['audience']
        );

        $response = Utility::fetchResponse($this->conn, $query, @$params, @$types);
        return $response;
    }

    // Get all pitches belonging to a specific user
    public function deletePitch($id)
    {
        $query = "DELETE FROM APP_pitch WHERE APP_pitch.id = ?;";

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

    // Get all pitches belonging to a specific user
    public function updatePitch($values)
    {
        $id = $values['target_id'];
        $num_updated = 0;
        $array_keys = array_keys($values);

        for ($loop = 0; $loop < count($values); $loop++) {

            $invalid = false;



            switch ($array_keys[$loop]) {
                case "pitch_title":
                    $query = "UPDATE APP_pitch SET pitch_title = ? WHERE id = ?";
                    $type = "si";
                    break;
                case "name":
                    $query = "UPDATE APP_pitch SET name = ? WHERE id = ?";
                    $type = "si";
                    break;
                case "genre_id":
                    $query = "UPDATE APP_pitch SET genre_id = ? WHERE id = ?";
                    $type = "ii";
                    break;
                case "description":
                    $query = "UPDATE APP_pitch SET description = ? WHERE id = ?";
                    $type = "si";
                    break;
                case "motive":
                    $query = "UPDATE APP_pitch SET motive = ? WHERE id = ?";
                    $type = "si";
                    break;
                case "audience":
                    $query = "UPDATE APP_pitch SET audience = ? WHERE id = ?";
                    $type = "si";
                    break;
                case "rating":

                    $old_data = $this->pitchData($id)['data'][0];
                    $new_num_ratings = $old_data['votes'] + 1;
                    $new_rating = (($old_data['rating'] *  $old_data['votes']) + $values[$array_keys[$loop]]) / $new_num_ratings;

                    // Recursive call to apply new rating info
                    $rating_update = $this->updatePitch([
                        "target_id" => $id,
                        "new_rating" => $new_rating,
                        "votes" => $new_num_ratings
                    ])['rows_updated'];

                    $num_updated += $rating_update;
                    $invalid = true;
                    break;
                case "new_rating":
                    $query = "UPDATE APP_pitch SET rating = ? WHERE id = ?";
                    $type = "di";
                    break;
                case "votes":
                    $query = "UPDATE APP_pitch SET votes = ? WHERE id = ?";
                    $type = "ii";
                    break;

                default:
                    $invalid = true;
                    break;
            }

            // Skip this iteration 
            if ($invalid == true) {
                continue;
            }

            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return Utility::errorReturn($this->conn->error);
            }

            $stmt->bind_param($type, $values[$array_keys[$loop]], $id);
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
}
