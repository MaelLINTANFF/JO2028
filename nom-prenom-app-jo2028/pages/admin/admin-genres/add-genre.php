<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Générer un token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

// Traitement du formulaire d'ajout (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $_SESSION['error'] = "Erreur de sécurité. Le formulaire n'est pas valide.";
        header("Location: add-genre.php");
        exit();
    }
    
    // Récupération et validation des données du formulaire
    $nom_genre = filter_input(INPUT_POST, 'nom_genre', FILTER_SANITIZE_STRING);

    // Vérification minimale des champs requis
    if (empty($nom_genre)) {
        $_SESSION['error'] = "Le nom du genre est obligatoire.";
        header("Location: add-genre.php");
        exit();
    }

    try {
        // Requête d'insertion
        $sql = "INSERT INTO GENRE (nom_genre) VALUES (:nom_genre)";
        
        $statement = $connexion->prepare($sql);

        // Liaison des paramètres
        $statement->bindParam(':nom_genre', $nom_genre);

        $statement->execute();

        // Message de succès et redirection
        $_SESSION['success'] = "Le genre a été ajouté avec succès.";
        header("Location: manage-genres.php");
        exit();

    } catch (PDOException $e) {
        // En cas de doublon (si nom_genre est unique)
        if ($e->getCode() == '23000') { 
            $_SESSION['error'] = "Ce genre existe déjà.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du genre : " . $e->getMessage();
        }
        header("Location: add-genre.php");
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
    <title>Ajouter un Genre - Jeux Olympiques - Los Angeles 2028</title>
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
                <li><a href="manage-genres.php">Gestion Genre</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter un Genre</h1>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="add-genre.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <label for="nom_genre">Nom du Genre (ex: Homme, Femme, Mixte) :</label>
            <input type="text" id="nom_genre" name="nom_genre" required maxlength="255">

            <button type="submit">Ajouter le Genre</button>
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-genres.php">Retour à la gestion des genres</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>