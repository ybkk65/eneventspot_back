<?php

namespace App\Controllers;

use App\Models\SqlConnect;
use PDO;
use PDOException;

class Inscription extends SqlConnect {
    protected array $params;
    protected string $reqMethod;

    public function __construct($params) {
        parent::__construct();
        $this->params = $params;
        $this->reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->run();
    }

    protected function getInscription() {
        $eventId = $_GET['event_id'];
        $userId = $_GET['userId'];

        try {
            $query = "SELECT COUNT(*) FROM inscription WHERE eventId = :eventId AND participant_id = :userId";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['eventId' => $eventId, 'userId' => $userId]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                return [
                    'error' => [
                        'message' => 'Vous êtes déjà inscrit à cet événement',
                    ]
                ];
            }

            return [
                'success' => [
                    'message' => 'Vous n\'êtes pas encore inscrit à cet événement'
                ]
            ];
        } catch (PDOException $e) {
            return [
                'error' => [
                    'message' => 'Erreur lors de la vérification de l\'inscription: ' . $e->getMessage()
                ]
            ];
        }
    }


    protected function postInscription() {
        try {
            $body = json_decode(file_get_contents('php://input'), true);
            $eventId = $body['event_id'];
            $userId = $body['userId'];
            $statut = 'envoyer';

            $query = "SELECT organiser_Id FROM event WHERE id = :eventId";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['eventId' => $eventId]);
            $result = $stmt->fetch();

            if ($result) {
                $organisateur_id = $result['organiser_Id'];

                if ($organisateur_id != $userId) {
                    $insertQuery = "INSERT INTO inscription (organisateur_id, participant_id, eventId, statut) VALUES (:organisateur_id, :participant_id, :eventId, :statut)";
                    $insertStmt = $this->db->prepare($insertQuery);
                    $insertStmt->execute([
                        'organisateur_id' => $organisateur_id,
                        'participant_id' => $userId,
                        'eventId'=> $eventId,
                        'statut' => $statut
                    ]);

                    return [
                        'success' => [
                            'message' => 'Inscription réussie'
                        ]
                    ];
                } else {
                    return [
                        'error' => [
                            'message' => 'Impossible de s\'inscrire à son propre événement'
                        ]
                    ];
                }
            } else {
                return [
                    'error' => [
                        'message' => 'Événement non trouvé'
                    ]
                ];
            }
        } catch (PDOException $e) {
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
        $method = $this->reqMethod . 'Inscription';

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
?>
