<?php

namespace App\Controllers;

use App\Models\SqlConnect;
use PDO;
use PDOException;

class Authentification extends SqlConnect {
    protected array $params;
    protected string $reqMethod;

    public function __construct($params) {
        parent::__construct();
        $this->params = $params;
        $this->reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->run();
    }

    protected function getAuthentification() {
        $headers = apache_request_headers();
        if (!isset($headers['Authorization'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Missing Authorization header']);
            exit;
        }

        $authHeader = $headers['Authorization'];
        $sessionId = str_replace('Bearer ', '', $authHeader);

        session_id($sessionId);
        session_start();

        if (session_id() == $sessionId && isset($_SESSION['user'])) {
            echo json_encode(['message' => 'Session is valid']);
        } else {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Invalid session']);
        }
    }

    protected function cors() {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: *");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }

            exit(0);
        }
    }

    protected function header() {
        header('Access-Control-Allow-Origin: *');
        header('Content-type: application/json; charset=utf-8');
        header("Access-Control-Allow-Headers: X-Requested-With, Authorization");
    }

    protected function ifMethodExist() {
        $method = $this->reqMethod . 'Authentification';

        if (method_exists($this, $method)) {
            $result = $this->$method();
            echo json_encode($result);
            return;
        }

        header('HTTP/1.0 404 Not Found');
        echo json_encode(['code' => '404', 'message' => 'Not Found']);
    }

    protected function run() {
        $this->cors();
        $this->header();
        $this->ifMethodExist();
    }
}
