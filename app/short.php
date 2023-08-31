<?php

require_once('private/config.php');
require_once('private/database.php');

class Short extends Database {
    public function __construct() {
        parent::__construct(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $this->connect();
    }

    public function RedirectShortURL($short_url) {
        list($original_url, $enable) = $this->findOriginalURL($short_url);
        
        if (!$original_url) {
            $this->sendJsonResponse(404, array('error' => "Not Found"));
        } 
        
        if (!$enable) {
            $this->sendJsonResponse(400, array('error' => "This URL has been deactivated"));
        } 

        header("Location: $original_url", true, 301);
        exit();
    }

    private function findOriginalURL($value) {
        $query = "SELECT * FROM SHORT WHERE BINARY short_url = ?";
        $params = [$value];
        $types = "s";

        $result = $this->executePreparedQuery($query, $params, $types);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return array($row['original_url'], $row['enable']);
        }

        return false;
    }

    private function sendJsonResponse($responseCode, $responseData) {
        http_response_code($responseCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(array(
            'status' => $responseCode,
            'data' => $responseData
        ), JSON_UNESCAPED_UNICODE);

        exit();
    }
}

$short = new Short;
$short->RedirectShortURL($_GET["url"] ?? '');
$short->close();

?>