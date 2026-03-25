<?php

function getConnection()
{
  $host = "localhost";
  $db_name = "gest_absence";
  $username = "pmauser";
  $password = "yourpassword";

  $conn = new mysqli($host, $username, $password, $db_name);
  if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => 0, "message" => "Erreur de connexion : $conn->connect_error"]);
    exit;
  }

  $conn->set_charset("utf8");
  return $conn;
}
