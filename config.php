<?php

$host = 'localhost';        
$dbname = 'bibliotheque';   
$username = 'root';         // Nom d'utilisateur (par défaut sous XAMPP/WAMP)
$password = '';             // Mot de passe (souvent vide en local)

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Activer les erreurs PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
