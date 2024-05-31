<?php

namespace App\Controllers;

use App\Models\SqlConnect;
use PDO;
use PDOException;

class Event_solo extends SqlConnect {
    protected array $params;
    protected string $reqMethod;

    public function __construct($params) {
        parent::__construct();
        $this->params = $params;
        $this->reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->run();
    }

    protected function getEvent_solo($id) {
        if ($id !== null) {
            try {
                $query = "SELECT * FROM event WHERE id = :id";
                $statement = $this->db->prepare($query);
                $statement->bindParam(':id', $id, PDO::PARAM_INT);
                $statement->execute();
                $event = $statement->fetch(PDO::FETCH_ASSOC);

                if ($event) {
                    
                    if (!empty($event['image'])) {
                        $event['image_base64'] = base64_encode($event['image']);
                        unset($event['image']);
                    }
                    return ["success" => true, "data" => $event];
                } else {
                    return ["success" => false, "message" => "Event not found"];
                }
            } catch (PDOException $e) {
                return ["success" => false, "message" => "PDO Error: " . $e->getMessage()];
            }
        } else {
            return ["success" => false, "message" => "No ID provided"];
        }
    }

    protected function postEvent_solo() {
        $body = $_POST;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['image']['tmp_name'];
            $imgData = file_get_contents($fileTmpName);
        } else {
            return ["success" => false, "message" => "Image upload error"];
        }

        $titre = $body['titre'] ?? null;
        $description = $body['description'] ?? null;
        $plus_info = $body['plus-info'] ?? null;
        $categorie = $body['categorie'] ?? null;
        $date = isset($body['date']) ? date('Y-m-d', strtotime($body['date'])) : null;
        $heure = isset($body['heure']) ? date('H:i', strtotime($body['heure'])) : null;
        $nombre_personne = isset($body['nombre-personne']) ? intval($body['nombre-personne']) : null;
        $ville = $body['ville'] ?? null;
        $phone = $body['phone'] ?? null;
        $prix_value = isset($body['prix_value']) ? intval($body['prix_value']) : null;
        $email = $body['email'] ?? null;
        $visibilite = $body['visibilite'] ?? null;
        $age = $body['age'] ?? null;
        $country = $body['country'] ?? null;
        $countryIcone = $body['countryIcon'] ?? null;
        $countryName = $body['countryName'] ?? null;
        $like = 0;

        try {
            $query = "INSERT INTO event (titre, description, description_plus, categorie, image, nbr_pers, prix, date, heure, ville, pays, num_tel, email, majorite, acces, `like`, country_icone, country_name) 
                      VALUES (:titre, :description, :description_plus, :categorie, :image, :nbr_pers, :prix, :date, :heure, :ville, :pays, :num_tel, :email, :majorite, :acces, :like, :country_icone, :country_name)";
            $statement = $this->db->prepare($query);
            $statement->bindParam(':titre', $titre);
            $statement->bindParam(':description', $description);
            $statement->bindParam(':description_plus', $plus_info);
            $statement->bindParam(':categorie', $categorie);
            $statement->bindParam(':image', $imgData, PDO::PARAM_LOB);
            $statement->bindParam(':nbr_pers', $nombre_personne);
            $statement->bindParam(':prix', $prix_value);
            $statement->bindParam(':date', $date);
            $statement->bindParam(':heure', $heure);
            $statement->bindParam(':ville', $ville);
            $statement->bindParam(':pays', $countryName);
            $statement->bindParam(':num_tel', $phone);
            $statement->bindParam(':email', $email);
            $statement->bindParam(':majorite', $age);
            $statement->bindParam(':acces', $visibilite);
            $statement->bindParam(':like', $like);
            $statement->bindParam(':country_icone', $countryIcone);
            $statement->bindParam(':country_name', $country);

            $statement->execute();
            return ["success" => true, "message" => "Event successfully inserted"];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "PDO Error: " . $e->getMessage()];
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
        $method = $this->reqMethod . 'Event_solo';

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
