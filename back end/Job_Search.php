<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'careerconnect_db');

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupérer les offres d'emploi
//$sql = "SELECT * FROM jobs ORDER BY created_at DESC";
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'careerconnect_db');

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupérer les critères de recherche
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';

// Construire la requête SQL en fonction des filtres
$sql = "SELECT * FROM jobs WHERE 1=1";

if (!empty($keyword)) {
    $sql .= " AND (title LIKE '%$keyword%' OR company LIKE '%$keyword%')";
}
if (!empty($location)) {
    $sql .= " AND location LIKE '%$location%'";
}

$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche d'Emploi</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Rechercher un Emploi</h2>

        <!-- Formulaire de recherche -->
        <form action="Job_Search.php" method="GET" class="my-4">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="keyword" class="form-control" placeholder="Mot-clé (ex: Développeur)">
                </div>
                <div class="col-md-4">
                    <input type="text" name="location" class="form-control" placeholder="Ville ou Wilaya">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                </div>
            </div>
        </form>

        <!-- Affichage des offres d'emploi -->
        <div class="row">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['title']); ?></h5>
                            <p class="card-text"><strong>Entreprise :</strong> <?= htmlspecialchars($row['company']); ?></p>
                            <p class="card-text"><strong>Localisation :</strong> <?= htmlspecialchars($row['location']); ?></p>
                            <p class="card-text"><strong>Salaire :</strong> <?= htmlspecialchars($row['salary']); ?></p>
                            <p class="card-text"><?= htmlspecialchars($row['description']); ?></p>
                            <a href="#" class="btn btn-success">Postuler</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="assets/JS/bootstrap.min.js"></script>
</body>

</html>