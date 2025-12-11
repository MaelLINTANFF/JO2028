<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID est fourni
if (!isset($_GET['id_genre'])) {
    $_SESSION['error'] = "ID de genre manquant.";
    header("Location: manage-genres.php");
    exit();
}

$id_genre = filter_input(INPUT_GET, 'id_genre', FILTER_VALIDATE_INT);

// Vérifiez si l'ID est valide
if ($id_genre === false) {
    $_SESSION['error'] = "ID de genre invalide.";
    header("Location: manage-genres.php");
    exit();
}

// 1. Récupération des données actuelles du genre (pour pré-remplissage)
try {
    $queryGenre = "SELECT nom_genre FROM GENRE WHERE id_genre = :id_genre";
    $statementGenre = $connexion->prepare($queryGenre);
    $statementGenre->bindParam(":id_genre", $id_genre, PDO::PARAM_INT);
    $statementGenre->execute();

    if ($statementGenre->rowCount() > 0) {
        $genre = $statementGenre->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Genre non trouvé.";
        header("Location: manage-genres.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données lors du chargement : " . $e->getMessage();
    header("Location: manage-genres.php");
    exit();
}

// 2. Traitement du formulaire de modification (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération et validation du nouveau nom
    $nouveau_nom = filter_input(INPUT_POST, 'nom_genre', FILTER_SANITIZE_STRING);

    // Vérification minimale
    if (empty($nouveau_nom)) {
        $_SESSION['error'] = "Le nom du genre ne peut pas être vide.";
        header("Location: modify-genre.php?id_genre=$id_genre");
        exit();
    }

    try {
        // Requête de mise à jour
        $sql = "UPDATE GENRE SET nom_genre = :nom_genre WHERE id_genre = :id_genre";
        
        $statement = $connexion->prepare($sql);

        // Liaison des paramètres
        $statement->bindParam(':nom_genre', $nouveau_nom);
        $statement->bindParam(':id_genre', $id_genre, PDO::PARAM_INT);

        $statement->execute();

        // Message de succès et redirection
        $_SESSION['success'] = "Le genre a été modifié avec succès.";
        header("Location: manage-genres.php");
        exit();

    } catch (PDOException $e) {
         if ($e->getCode() == '23000') { 
            $_SESSION['error'] = "Ce genre existe déjà.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du genre : " . $e->getMessage();
        }
        header("Location: modify-genre.php?id_genre=$id_genre");
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
    <title>Modifier un Genre - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Modifier le Genre</h1>
        <h2>Genre actuel : <?php echo htmlspecialchars($genre['nom_genre']); ?></h2>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="modify-genre.php?id_genre=<?php echo $id_genre; ?>" method="post">
            
            <label for="nom_genre">Nouveau Nom du Genre :</label>
            <input type="text" id="nom_genre" name="nom_genre" 
                   value="<?php echo htmlspecialchars($genre['nom_genre'], ENT_QUOTES, 'UTF-8'); ?>" required maxlength="255">

            <button type="submit">Modifier le Genre</button>
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