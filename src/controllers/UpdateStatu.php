<?php

namespace App\Controllers;

use App\Models\SqlConnect;
use PDO;
use PDOException;

class UpdateStatu extends SqlConnect {
    protected array $params;
    protected string $reqMethod;

    public function __construct($params) {
        parent::__construct();
        $this->params = $params;
        $this->reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->run();
    }
    protected function postUpdateStatu() {
        $requestData = json_decode(file_get_contents('php://input'), true);
        
        if (!$requestData || !isset($requestData['idInscription']) || !isset($requestData['statut'])) {
            echo json_encode(['code' => 400, 'message' => 'Données manquantes ou malformées']);
            return;
        }
        
        $idInscription = $requestData['idInscription'];
        $statut = $requestData['statut'];
        
        try {
            $stmt = $this->db->prepare("UPDATE inscription SET statut = :statut WHERE id = :id");
            $stmt->bindParam(':statut', $statut);
            $stmt->bindParam(':id', $idInscription, PDO::PARAM_INT); // Assurez-vous que idInscription est un entier
            
            if ($stmt->execute()) {
                echo json_encode(['code' => 200, 'message' => 'Statut mis à jour avec succès']);
            } else {
                echo json_encode(['code' => 500, 'message' => 'Erreur lors de la mise à jour du statut']);
            }
        } catch (PDOException $e) {
            echo json_encode(['code' => 500, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
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
        $method = $this->reqMethod . 'UpdateStatu';

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


