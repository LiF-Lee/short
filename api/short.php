<?php

require_once('private/config.php');
require_once('private/database.php');

class Short extends Database {
    public function __construct() {
        parent::__construct(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $this->connect();
    }

    public function test() {
        $query = "SELECT * FROM SHORT WHERE id = ?";
        $params = array(3);
        $types = "i";
        $result = $this->executePreparedQuery($query, $params, $types);

        while ($row = mysqli_fetch_array($result)) {
            echo $row['id'] . " " . $row['create_time'] . " " . $row['original_url'] . " " . $row['short_url'] . " " . $row['enable'] . "<br>";
        }
    }
}

$short = new Short;
$short->test();
$short->close();

?>