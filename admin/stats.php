<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
$db = getConnection();

function getStats($db)
{
  $result = ["total_etudiants" => 0, "total_enseignants" => 0, "absences_jour" => 0];

  $queryEtu = "SELECT COUNT(*) as total FROM etudiants";
  $resEtu = $db->query($queryEtu);
  if ($resEtu) {
    $row = $resEtu->fetch_assoc();
    $result["total_etudiants"] = (int) $row["total"];
  }

  $queryEns = "SELECT COUNT(*) as total FROM enseignants";
  $resEns = $db->query($queryEns);
  if ($resEns) {
    $row = $resEns->fetch_assoc();
    $result["total_enseignants"] = (int) $row["total"];
  }

  $queryAbs = "SELECT COUNT(a.id) as total FROM absences a JOIN seances s ON a.seance_id = s.id WHERE s.date_seance = CURDATE()";
  $resAbs = $db->query($queryAbs);
  if ($resAbs) {
    $row = $resAbs->fetch_assoc();
    $result["absences_jour"] = (int) $row["total"];
  }

  echo json_encode(["success" => 1, "data" => $result]);
}

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    getStats($db);
    break;
  case 'OPTIONS':
    http_response_code(204);
    break;
  default:
    http_response_code(405);
    echo json_encode(["success" => 0, "message" => "Méthode non autorisée"]);
    break;
}

