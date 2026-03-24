<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Compter les étudiants (Table etudiants)
    $resEtu = $db->query("SELECT COUNT(*) as total FROM etudiants")->fetch(PDO::FETCH_ASSOC);
    
    // 2. Compter les enseignants (Table enseignants)
    $resEns = $db->query("SELECT COUNT(*) as total FROM enseignants")->fetch(PDO::FETCH_ASSOC);
    
    // 3. Compter les absences dont la SEANCE est à la date d'aujourd'hui
    $queryAbs = "SELECT COUNT(a.id) as total 
                 FROM absences a 
                 JOIN seances s ON a.seance_id = s.id 
                 WHERE s.date_seance = CURDATE()";
    $resAbs = $db->query($queryAbs)->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "total_etudiants" => $resEtu['total'],
        "total_enseignants" => $resEns['total'],
        "absences_jour" => $resAbs['total']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>