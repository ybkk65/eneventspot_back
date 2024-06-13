<?php

namespace App\Controllers;

use App\Models\SqlConnect;
use PDO;
use PDOException;

class MyInscription extends SqlConnect {
    protected array $params;
    protected string $reqMethod;

    public function __construct($params) {
        parent::__construct();
        $this->params = $params;
        $this->reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->run();
    }

    protected function getMyInscription($id) {
        if ($id !== null) {
            try {
                // Récupérer les inscriptions du participant
                $query = "SELECT eventId, statut FROM inscription WHERE participant_id = :id";
                $statement = $this->db->prepare($query);
                $statement->bindParam(':id', $id, PDO::PARAM_INT);
                $statement->execute();
                $inscriptions = $statement->fetchAll(PDO::FETCH_ASSOC);

               
                if (!$inscriptions) {
                    return ["success" => false, "message" => "Aucune inscription trouvée pour ce participant"];
                }

                $events = [];
                foreach ($inscriptions as $inscription) {
                    
                    $query = "SELECT * FROM event WHERE id = :eventId";
                    $statement = $this->db->prepare($query);
                    $statement->bindParam(':eventId', $inscription['eventId'], PDO::PARAM_INT);
                    $statement->execute();
                    $event = $statement->fetch(PDO::FETCH_ASSOC);

                    if ($event) {
                        $event['statut'] = $inscription['statut'];

                        $imageBase64 = base64_encode($event['image']);
                        unset($event['image']);
                        $event['image_base64'] = $imageBase64;

                        $events[] = $event;
                    }
                }

                return ["success" => true, "data" => $events];
            } catch (PDOException $e) {
                return ["success" => false, "message" => "Erreur lors de la récupération des événements: " . $e->getMessage()];
            }
        } else {
            return ["success" => false, "message" => "Aucun ID fourni"];
        }
    }

    protected function postMy_Event() {
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
        $method = $this->reqMethod .'MyInscription';

        if (method_exists($this, $method)) {
            echo json_encode($this->$method($this->params['id']));
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
