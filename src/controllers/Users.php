<?php

namespace App\Controllers;

use App\Models\SqlConnect;
use PDO;
use PDOException;

class Users extends SqlConnect {
    protected array $params;
    protected string $reqMethod;

    public function __construct($params) {
        parent::__construct();
        $this->params = $params;
        $this->reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->run();
    }

    protected function getUsers() {
        $query = "SELECT id, firstname, email FROM users";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $users = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = [
                    'id' => $row['id'],
                    'firstname' => $row['firstname'],
                    'email' => $row['email']
                ];
                $users[] = $user;
            }
            
            return $users;
        } catch(PDOException $e) {
            return [
                'error' => [
                    'message' => 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage()
                ]
            ];
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
        $method = $this->reqMethod . 'Users';

        if (method_exists($this, $method)) {
            echo json_encode($this->$method());
            return;
        }

        header('HTTP/1.0 404 Not Found');
        echo json_encode([
            'code' => '404',
            'message' => 'Not Found'
        ]);
    }

    protected function run() {
        $this->cors();
        $this->header();
        $this->ifMethodExist();
    }
}
