<?php
$host = "localhost";
$dbname = "careerconnect";
$username = "root"; // Par défaut, c’est root sur XAMPP
$password = ""; // Pas de mot de passe sur XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
