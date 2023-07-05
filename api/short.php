<?php

require_once('private/config.php');
require_once('private/database.php');

class Short extends Database {
    public function __construct() {
        parent::__construct(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $this->connect();
    }

    public function CreateShortURL($original_url) {
        $this->validateURL($original_url);

        if ($existingShortURL = $this->findShortURL($original_url)) {
            $this->sendJsonResponse(200, array('original_url' => $original_url, 'short_url' => 'https://s.leesj.co/' . $existingShortURL));
        } else {
            $short_url = $this->createUniqueShortURL();

            $this->executePreparedQuery(
                "INSERT INTO SHORT (original_url, short_url) VALUES (?, ?)", 
                array($original_url, $short_url), 
                "ss"
            );

            $this->sendJsonResponse(200, array('original_url' => $original_url, 'short_url' => 'https://s.leesj.co/' . $short_url));
        }
    }

    private function validateURL($url) {
        if (!preg_match('/^[a-z]+:\/\/.+$/', $url)) {
            $this->sendJsonResponse(400, array('error' => "Invalid URL ('" . $url . "')"));
        }
    }

    private function findOriginalURL($value) {
        $query = "SELECT * FROM SHORT WHERE BINARY short_url = ?";
        $params = [$value];
        $types = "s";

        $result = $this->executePreparedQuery($query, $params, $types);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row["original_url"];
        }

        return false;
    }

    private function findShortURL($value) {
        $query = "SELECT * FROM SHORT WHERE original_url = ?";
        $params = [$value];
        $types = "s";

        $result = $this->executePreparedQuery($query, $params, $types);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row["short_url"];
        }

        return false;
    }

    private function createUniqueShortURL() {
        $short_url = $this->generateRandomString(5);

        if (!$this->findOriginalURL($short_url)) {
            return $short_url;
        } else {
            return $this->createUniqueShortURL();
        }
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
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
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);
$short->CreateShortURL($data["url"] ?? '');
$short->close();

?>