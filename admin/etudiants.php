<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
$db = getConnection();
$data = json_decode(file_get_contents("php://input"));

function getEtudiants($db)
{
  $query = "SELECT u.id as user_id, u.role , e.id as etudiant_id, u.nom as nom, u.prenom as prenom, u.email as email, c.nom as classe_nom 
              FROM etudiants e 
              JOIN utilisateurs u ON e.utilisateur_id = u.id 
              JOIN classes c ON e.classe_id = c.id";
  $result = $db->query($query);
  $etudiants = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  $etudiants = array_map(function ($row) {
    $row['user_id'] = (int) $row['user_id'];
    $row['etudiant_id'] = (int) $row['etudiant_id'];
    return $row;
  }, $etudiants);
  echo json_encode(["success" => 1, "data" => $etudiants]);
}

function createEtudiant($db, $data)
{
  if (empty($data->nom) || empty($data->prenom) || empty($data->email) || empty($data->password) || empty($data->classe_id)) {
    echo json_encode(["success" => 0, "message" => "Données incomplètes"]);
    return;
  }

  try {
    $db->begin_transaction();

    $queryUser = "INSERT INTO utilisateurs (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, 'etudiant')";
    $stmtUser = $db->prepare($queryUser);
    $stmtUser->bind_param('ssss', $data->nom, $data->prenom, $data->email, $data->password);
    $stmtUser->execute();

    $userId = $db->insert_id;

    $queryEtu = "INSERT INTO etudiants (utilisateur_id, classe_id) VALUES (?, ?)";
    $stmtEtu = $db->prepare($queryEtu);
    $stmtEtu->bind_param('ii', $userId, $data->classe_id);
    $stmtEtu->execute();

    $db->commit();
    echo json_encode(["success" => 1, "message" => "Étudiant ajouté avec succès"]);
  } catch (Exception $e) {
    $db->rollback();
    echo json_encode(["success" => 0, "message" => "Erreur : " . $e->getMessage()]);
  }
}

function updateEtudiant($db, $data)
{
  if (empty($data->utilisateur_id) || empty($data->nom) || empty($data->prenom) || empty($data->classe_id)) {
    echo json_encode(["success" => 0, "message" => "Données incomplètes pour mise à jour"]);
    return;
  }

  try {
    $db->begin_transaction();

    $qUser = "UPDATE utilisateurs SET nom = ?, prenom = ? WHERE id = ?";
    $stUser = $db->prepare($qUser);
    $stUser->bind_param('ssi', $data->nom, $data->prenom, $data->utilisateur_id);
    $stUser->execute();

    $qEtu = "UPDATE etudiants SET classe_id = ? WHERE utilisateur_id = ?";
    $stEtu = $db->prepare($qEtu);
    $stEtu->bind_param('ii', $data->classe_id, $data->utilisateur_id);
    $stEtu->execute();

    $db->commit();
    echo json_encode(["success" => 1, "message" => "Étudiant mis à jour"]);
  } catch (Exception $e) {
    $db->rollback();
    echo json_encode(["success" => 0, "message" => "Erreur : " . $e->getMessage()]);
  }
}

function deleteEtudiant($db, $data)
{
  if (empty($data->utilisateur_id)) {
    echo json_encode(["success" => 0, "message" => "ID utilisateur d'étudiant manquant"]);
    return;
  }

  try {
    $db->begin_transaction();

    $qEtu = "DELETE FROM etudiants WHERE utilisateur_id = ?";
    $stEtu = $db->prepare($qEtu);
    $stEtu->bind_param('i', $data->utilisateur_id);
    $stEtu->execute();

    $qUser = "DELETE FROM utilisateurs WHERE id = ?";
    $stUser = $db->prepare($qUser);
    $stUser->bind_param('i', $data->utilisateur_id);
    $stUser->execute();

    $db->commit();
    echo json_encode(["success" => 1, "message" => "Étudiant supprimé"]);
  } catch (Exception $e) {
    $db->rollback();
    echo json_encode(["success" => 0, "message" => "Erreur : " . $e->getMessage()]);
  }
}

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    getEtudiants($db);
    break;
  case 'POST':
    createEtudiant($db, $data);
    break;
  case 'PUT':
    updateEtudiant($db, $data);
    break;
  case 'DELETE':
    deleteEtudiant($db, $data);
    break;
}

