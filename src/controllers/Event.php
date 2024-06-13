<?php

namespace App\Controllers;

use App\Models\SqlConnect;
use PDO;
use PDOException;

class Event extends SqlConnect {
    protected array $params;
    protected string $reqMethod;

    public function __construct($params) {
        parent::__construct();
        $this->params = $params;
        $this->reqMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->run();
    }

    protected function getEvent() {
        try {
            $query = "SELECT * FROM event";
            $statement = $this->db->prepare($query);
            $statement->execute();
            $events = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($events as &$event) {
                $imageBase64 = base64_encode($event['image']);
                unset($event['image']);
                $event['image_base64'] = $imageBase64;
            }

            return ["success" => true, "data" => $events];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Erreur lors de la récupération des événements: " . $e->getMessage()];
        }
    }

    protected function postEvent() {
        
        $body = $_POST;

        if (isset($_FILES['image'])) {
            $file = $_FILES['image'];

            if ($file['error'] === UPLOAD_ERR_OK) {
                $fileTmpName = $file['tmp_name'];
                $imgData = file_get_contents($fileTmpName);
            } else {
                return ["success" => false, "message" => "Erreur lors du téléchargement de l'image"];
            }
        } else {
            return ["success" => false, "message" => "Aucune image envoyée"];
        }

        // Récupérer les données du corps de la requête
        $organiserId = $body['userId'];
        $selectedPerson = $body['selectedPersons'];
        $titre = $body['titre'];
        $description = $body['description'];
        $plus_info = $body['plus-info'];
        $categorie = $body['categorie'];
        $date = date('Y-m-d', strtotime($body['date']));
        $heure = date('H:i', strtotime($body['heure']));
        $nombre_personne = intval($body['nombre-personne']);
        $ville = $body['ville'];
        $phone = $body['phone'];
        $prix_value = intval($body['prix_value']);
        $email = $body['email'];
        $visibilite = $body['visibilite'];
        $age = $body['age'];
        $country = $body['country'];
        $countryIcone = $body['countryIcon'];
        $countryName = $body['countryName'];
        $like = 0;
        $state = "envoyer";

        try {
            // Insérer les données de l'événement dans la table event
            $query = "INSERT INTO event (organiser_Id, titre, description, description_plus, categorie, image, nbr_pers, prix, date, heure, ville, pays, num_tel, email, majorite, acces, `like`, country_icone, country_name) VALUES (:organiser_Id, :titre, :description, :description_plus, :categorie, :image, :nbr_pers, :prix, :date, :heure, :ville, :pays, :num_tel, :email, :majorite, :acces, :like, :country_icone, :country_name)";
            $statement = $this->db->prepare($query);
            $statement->bindParam(':organiser_Id',$organiserId);
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
            $eventid = $this->db->lastInsertId();

            // Insérer les données d'invitation dans la table invitation
            $queryInvitation = "INSERT INTO invitation (organiser_id, invite_list, event_id, statu) VALUES (:organiser_id, :invite_list, :event_id, :statu)";
            $statementInvitation = $this->db->prepare($queryInvitation);
            $statementInvitation->bindParam(':organiser_id', $organiserId);
            $statementInvitation->bindParam(':invite_list', $selectedPerson);
            $statementInvitation->bindParam(':event_id', $eventid);
            $statementInvitation->bindParam(':statu', $state);

            $statementInvitation->execute();

            return ["success" => true, "message" => "Événement créé avec succès"];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Erreur lors de la création de l'événement: " . $e->getMessage()];
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
        $method = $this->reqMethod . 'Event';

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
