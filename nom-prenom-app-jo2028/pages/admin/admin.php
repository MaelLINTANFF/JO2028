<?php
session_start();
require_once("../../database/database.php"); 

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../index.php');
    exit();
}

// Récupération sécurisée des informations utilisateur

$login = $_SESSION['login'];
$nom_utilisateur = $_SESSION['prenom_admin'] ?? "Administrateur";
$prenom_utilisateur = $_SESSION['nom_admin'] ?? "";

error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/normalize.css">
    <link rel="stylesheet" href="../../css/styles-computer.css">
    <link rel="stylesheet" href="../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../img/favicon.ico" type="image/x-icon">
    <title>Accueil Administration - Jeux Olympiques - Los Angeles 2028</title>
</head>

<body>
    <header>
        <nav>
            <ul class="menu">
                <li><a href="admin.php">Accueil Administration</a></li> 
                <li><a href="./admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="./admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="./admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="./admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="./admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="./admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../admin-genres/manage-genres.php">Gestion Genres</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li> 
            </ul>
        </nav>
    </header>

    <main>
        <h1>Tableau de bord de l'Administration</h1>
        <p class="info-login">
            Bonjour, <?php echo htmlspecialchars($nom_utilisateur . " " . $prenom_utilisateur); ?>
        </p>
        
        <div class="management-links" 
             style="display:flex; flex-wrap:wrap; gap:40px; justify-content:center;">
            
            <a class="link-category" href="./admin-users/manage-users.php">Gestion Administrateurs</a>
            <a class="link-category" href="./admin-sports/manage-sports.php">Gestion Sports</a>
            <a class="link-category" href="./admin-places/manage-places.php">Gestion Lieux</a>
            <a class="link-category" href="./admin-countries/manage-countries.php">Gestion Pays</a>
            <a class="link-category" href="./admin-events/manage-events.php">Gestion Calendrier</a>
            <a class="link-category" href="./admin-genres/manage-genres.php">Gestion Genre</a> 
            <a class="link-category" href="./admin-athletes/manage-athletes.php">Gestion Athlètes</a>
            <a class="link-category" href="./admin-results/manage-results.php">Gestion Résultats</a>

        </div>
    </main>

    <footer>
        <figure>
            <img src="../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>

</html>
