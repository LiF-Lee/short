<?php

require_once('config.php');
require_once('database.php');

class Short extends Database {
    public function __construct() {
        parent::__construct(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    }
}

?>