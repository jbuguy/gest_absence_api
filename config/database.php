<?php
function getConnection() {
    $host = "localhost";
    $db_name = "gest_absence"; // Vérifie que c'est bien le nom de ta base dans phpMyAdmin
    $username = "root";        // Change 'pmauser' par 'root'
    $password = "";            // Laisse vide (souvent vide par défaut sur XAMPP)

    try {
        $conn = new mysqli($host, $username, $password, $db_name);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        return $conn;
    } catch (Exception $exception) {
        echo "Error: " . $exception->getMessage();
        return null;
    }
}
?>