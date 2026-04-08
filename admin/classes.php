<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$db = getConnection();
$data = json_decode(file_get_contents("php://input"));

function getClasses($db)
{
  $query = "SELECT * FROM classes";
  $result = $db->query($query);
  $classes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  $classes = array_map(function ($row) {
    $row["id"] = (int) $row["id"];
    return $row;
  }, $classes);
  echo json_encode(["success" => 1, "data" => $classes]);
}

function createClasse($db, $data)
{
  if (empty($data->nom) || empty($data->niveau)) {
    echo json_encode(["success" => 0, "message" => "Données incomplètes"]);
    return;
  }

  $query = "INSERT INTO classes (nom, niveau) VALUES (?, ?)";
  $stmt = $db->prepare($query);
  $stmt->bind_param('ss', $data->nom, $data->niveau);

  if ($stmt->execute()) {
    echo json_encode(["success" => 1, "message" => "Classe créée"]);
  } else {
    echo json_encode(["success" => 0, "message" => "Erreur SQL : " . $stmt->error]);
  }
}

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    getClasses($db);
    break;
  case 'POST':
    createClasse($db, $data);
    break;
}

