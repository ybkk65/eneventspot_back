<?php

namespace App\Controllers;

use App\Models\SqlConnect;
use PDO;
use PDOException;

class Register extends SqlConnect {
    protected array $params;
    protected string $reqMethod;

    public function __construct($params) {
        parent::__construct();
        $this->params = $params;
        $this->reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->run();
    }

    protected function getRegister() {
        
    }

    protected function postRegister() {
        $data = json_decode(file_get_contents('php://input'), true);
    
        $requiredFields = ['nom', 'prenom', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['message' => 'Tous les champs sont requis.']);
                return;
            }
        }
    
        
        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $email = $data['email'];
        $password = $data['password'];
    
       
        if (!preg_match('/^[a-zA-Z]+$/', $nom) || !preg_match('/^[a-zA-Z]+$/', $prenom)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['message' => 'Le nom et le prénom doivent contenir uniquement des lettres.']);
            return;
        }
    
       
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['message' => 'Adresse email invalide.']);
            return;
        }
    

       
       $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    
        try {
            
            $query = "INSERT INTO users (firstname, lastname, email, password) VALUES (:nom, :prenom, :email, :password)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
    
            header('HTTP/1.1 201 Created');
            echo json_encode(['message' => 'Utilisateur créé avec succès.']);
        } catch (PDOException $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['message' => 'Erreur lors de la création de l\'utilisateur.']);
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
        $method = $this->reqMethod . 'Register';

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
