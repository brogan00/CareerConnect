<?php
require 'includes/db.php';
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "recruiter") {
    die("Accès refusé.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $company = $_POST["company"];
    $location = $_POST["location"];
    $salary = $_POST["salary"];

    $sql = "INSERT INTO jobs (title, description, company, location, salary) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description, $company, $location, $salary]);

    echo "Offre publiée avec succès !";
}
?>

<form action="post_job.php" method="POST">
    <input type="text" name="title" placeholder="Titre du poste" required>
    <textarea name="description" placeholder="Description" required></textarea>
    <input type="text" name="company" placeholder="Entreprise" required>
    <input type="text" name="location" placeholder="Lieu" required>
    <input type="text" name="salary" placeholder="Salaire">
    <button type="submit">Publier</button>
</form>