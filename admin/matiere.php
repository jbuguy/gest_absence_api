<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$db = getConnection();

function getMatieres($db)
{
    $query = "SELECT id, nom FROM matieres";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $matieres = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $matieres = array_map(function ($row) {
        $row['id'] = (int) $row['id'];
        return $row;
    }, $matieres);

    echo json_encode(["success" => 1, "data" => $matieres]);
}

getMatieres($db);
