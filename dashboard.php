<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

echo "Bienvenue sur CareerConnect !";
echo "<a href='logout.php'>Se déconnecter</a>";
