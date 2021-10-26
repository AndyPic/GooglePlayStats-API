<?php

namespace classes;

/**
 * Database connection class
 */
class DatabaseConnection
{

    private $conn = null;

    public function __construct()
    {
        $pw = "pCC3JJLplyN85vzv";
        $username = "apickard01";
        $db = "apickard01";
        $host = "apickard01.lampt.eeecs.qub.ac.uk";

        try {
            $this->conn = new \mysqli($host, $username, $pw, $db);
        } catch (\Exception $e) {
            exit("Failed to connect to database \n".$e->getMessage());
        }
    }

    /**
     * Get a connection to the DB
     */
    public function getConn()
    {
        return $this->conn;
    }
} // END
