<?php

namespace App\Controllers;

use App\Models\SqlConnect;
use PDO;
use PDOException;

class Login extends SqlConnect {
    protected array $params;
    protected string $reqMethod;

    public function __construct($params) {
        parent::__construct();
        $this->params = $params;
        $this->reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->run();
    }

    protected function postLogin() {
        $requestData = json_decode(file_get_contents('php://input'), true);

        if (!isset($requestData['email']) || !isset($requestData['password'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Missing email or password']);
            exit;
        }

        $email = $requestData['email'];
        $password = $requestData['password'];

        try {
            $stmt = $this->db->prepare("SELECT email, password FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $userPassword = $user['password'];
                if (password_verify($password, $userPassword)) {
                    echo json_encode(['message' => 'Login successful']);
                } else {
                    header('HTTP/1.0 401 Unauthorized');
                    echo json_encode(['error' => 'Invalid password']);
                }
            } else {
                header('HTTP/1.0 401 Unauthorized');
                echo json_encode(['error' => 'User not found']);
            }
        } catch (PDOException $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
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
        header("Access-Control-Allow-Headers: X-Requested-With");
    }

    protected function ifMethodExist() {
        $method = $this->reqMethod . 'Login';

        if (method_exists($this, $method)) {
            echo json_encode($this->$method());
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
