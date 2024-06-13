<?php

namespace App\Controllers;

use App\Models\SqlConnect;
use PDO;
use PDOException;

class Inscription_status extends SqlConnect {
    protected array $params;
    protected string $reqMethod;

    public function __construct($params) {
        parent::__construct();
        $this->params = $params;
        $this->reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->run();
    }

    protected function getInscriptionStatus($id) {
        if ($id !== null) {
            try {
                $query = "SELECT * FROM inscription WHERE organisateur_id = :id";
                $statement = $this->db->prepare($query);
                $statement->bindParam(':id', $id, PDO::PARAM_INT);
                $statement->execute();
                $inscriptions = $statement->fetchAll(PDO::FETCH_ASSOC);

                if (!$inscriptions) {
                    return ["success" => false, "message" => "Aucune inscription trouvée pour cet organisateur"];
                }

                $result = [];
                foreach ($inscriptions as $inscription) {
                    $event = $this->getEventDetails($inscription['eventId']);
                    $user = $this->getUserDetails($inscription['participant_id']);

                    if ($event && $user) {
                        $event['id_inscription'] = $inscription['id']; // Ajout de l'ID de l'inscription
                        $event['statut'] = $inscription['statut'];
                        $event['participant_firstname'] = $user['firstname'];
                        $event['participant_lastname'] = $user['lastname'];

                        if (isset($event['image'])) {
                            $event['image_base64'] = base64_encode($event['image']);
                            unset($event['image']);
                        }

                        $result[] = $event;
                    }
                }

                return ["success" => true, "data" => $result];
            } catch (PDOException $e) {
                return ["success" => false, "message" => "Erreur lors de la récupération des événements: " . $e->getMessage()];
            }
        } else {
            return ["success" => false, "message" => "Aucun ID fourni"];
        }
    }

    protected function getEventDetails($eventId) {
        $query = "SELECT * FROM event WHERE id = :eventId";
        $statement = $this->db->prepare($query);
        $statement->bindParam(':eventId', $eventId, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    protected function getUserDetails($userId) {
        $query = "SELECT firstname, lastname FROM users WHERE id = :userId";
        $statement = $this->db->prepare($query);
        $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
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
        $method = $this->reqMethod . 'InscriptionStatus';

        if (method_exists($this, $method)) {
            echo json_encode($this->$method($this->params['id']));
            return;
        }

        http_response_code(404);
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
