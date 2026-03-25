<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/database.php';
$db = getConnection();

function login($db, $data)
{
  if (empty($data->email) || empty($data->password)) {
    echo json_encode(["success" => 0, "message" => "Données incomplètes."]);
    return;
  }

  $query = "SELECT id, nom, prenom, role FROM utilisateurs 
              WHERE email = ? AND password = ? LIMIT 1";

  $stmt = $db->prepare($query);
  $stmt->bind_param('ss', $data->email, $data->password);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
      "success" => 1,
      "user" => $row
    ]);
  } else {
    echo json_encode(["success" => 0, "message" => "Email ou mot de passe incorrect."]);
  }
}

$data = json_decode(file_get_contents("php://input"));
login($db, $data);
