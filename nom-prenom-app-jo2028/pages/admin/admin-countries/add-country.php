<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vider les messages de succès précédents
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Initialisation de la variable pour le formulaire
$nomPays = '';

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Récupération et filtration des données
    $nomPays = filter_input(INPUT_POST, 'nomPays', FILTER_SANITIZE_SPECIAL_CHARS);

    // 2. Vérification du champ vide
    if (empty($nomPays)) {
        $_SESSION['error'] = "Le nom du pays est obligatoire.";
        // Redirection vers le formulaire actuel pour réaffichage (si besoin de conserver l'erreur)
        header("Location: add-country.php");
        exit();
    }

    try {
        // 3. Vérification si le pays existe déjà par son nom
        $queryCheck = "SELECT id_pays FROM PAYS WHERE nom_pays = :nomPays";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomPays", $nomPays, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Ce pays existe déjà dans la base de données.";
            header("Location: add-country.php");
            exit();
        }

        // 4. Requête d'insertion
        $query = "INSERT INTO PAYS (nom_pays) VALUES (:nomPays)";
        
        $statement = $connexion->prepare($query);
        
        // 5. Liaison du paramètre
        $statement->bindParam(":nomPays", $nomPays, PDO::PARAM_STR);
        
        // 6. Exécution de la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le pays **" . htmlspecialchars($nomPays, ENT_QUOTES, 'UTF-8') . "** a été ajouté avec succès.";
            // Redirection vers la page de gestion des pays
            header("Location: manage-countries.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du pays.";
            header("Location: add-country.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-country.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon.ico" type="image/x-icon">
    <title>Ajouter un Pays - Jeux Olympiques - Los Angeles 2028</title>
</head>

<body>
    <header>
        <nav>
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Ajouter un Pays</h1>
        
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['success']);
        }
        ?>

        <form action="add-country.php" method="post">
            
            <label for="nomPays">Nom du Pays :</label>
            <input type="text" name="nomPays" id="nomPays" value="<?php echo htmlspecialchars($nomPays, ENT_QUOTES, 'UTF-8'); ?>" required>

            <input type="submit" value="Ajouter le Pays">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-countries.php">Retour à la gestion des pays</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>

</html>