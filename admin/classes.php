<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $query = "SELECT * FROM classes";
        $stmt = $db->prepare($query);
        $stmt->execute();
        echo json_encode(["success" => 1, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->nom) && !empty($data->niveau)) {
            $query = "INSERT INTO classes (nom, niveau) VALUES (:nom, :niveau)";
            $stmt = $db->prepare($query);
            $stmt->execute([':nom' => $data->nom, ':niveau' => $data->niveau]);
            echo json_encode(["success" => 1, "message" => "Classe créée"]);
        }
        break;
}