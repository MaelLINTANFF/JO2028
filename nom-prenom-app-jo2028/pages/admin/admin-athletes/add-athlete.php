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

// 1. Récupération des listes de pays et de genres pour les menus déroulants
$countries = [];
$genres = [];
try {
    // Récupérer tous les pays
    $query_countries = "SELECT id_pays, nom_pays FROM PAYS ORDER BY nom_pays";
    $statement_countries = $connexion->query($query_countries);
    $countries = $statement_countries->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les genres/sexes (de la table GENRE)
    $query_genres = "SELECT id_genre, nom_genre FROM GENRE ORDER BY nom_genre";
    $statement_genres = $connexion->query($query_genres);
    $genres = $statement_genres->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors du chargement des listes : " . $e->getMessage();
    header("Location: manage-athletes.php");
    exit();
}


// 2. Traitement du formulaire d'ajout (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $_SESSION['error'] = "Erreur de sécurité. Le formulaire n'est pas valide.";
        header("Location: add-athlete.php");
        exit();
    }
    
    // Récupération et validation des données du formulaire
    $nom_athlete = filter_input(INPUT_POST, 'nom_athlete', FILTER_SANITIZE_STRING);
    $prenom_athlete = filter_input(INPUT_POST, 'prenom_athlete', FILTER_SANITIZE_STRING);
    // CORRIGÉ : On récupère l'ID du genre sélectionné
    $id_genre = filter_input(INPUT_POST, 'id_genre', FILTER_VALIDATE_INT); 
    $id_pays = filter_input(INPUT_POST, 'id_pays', FILTER_VALIDATE_INT);

    // Vérification minimale des champs requis
    if (empty($nom_athlete) || empty($prenom_athlete) || $id_genre === false || $id_pays === false) {
        $_SESSION['error'] = "Tous les champs sont obligatoires et doivent être valides.";
        header("Location: add-athlete.php");
        exit();
    }
    
    try {
        // Requête d'insertion dans la table ATHLETE (CORRIGÉE pour id_genre)
        $sql = "INSERT INTO ATHLETE (nom_athlete, prenom_athlete, id_genre, id_pays) 
                VALUES (:nom_athlete, :prenom_athlete, :id_genre, :id_pays)";
        
        $statement = $connexion->prepare($sql);

        // Liaison des paramètres
        $statement->bindParam(':nom_athlete', $nom_athlete);
        $statement->bindParam(':prenom_athlete', $prenom_athlete);
        $statement->bindParam(':id_genre', $id_genre, PDO::PARAM_INT); // Utilisation de l'ID
        $statement->bindParam(':id_pays', $id_pays, PDO::PARAM_INT);

        $statement->execute();

        // Message de succès et redirection
        $_SESSION['success'] = "L'athlète **" . htmlspecialchars($prenom_athlete . " " . $nom_athlete) . "** a été ajouté avec succès.";
        header("Location: manage-athletes.php");
        exit();

    } catch (PDOException $e) {
        // En cas d'erreur (doublon, clé étrangère manquante, etc.)
        $_SESSION['error'] = "Erreur lors de l'ajout de l'athlète : " . $e->getMessage();
        header("Location: add-athlete.php");
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
    <title>Ajouter un Athlète - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Ajouter un Athlète</h1>

        <?php
        // Afficher les messages d'erreur s'ils existent
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="add-athlete.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <label for="nom_athlete">Nom :</label>
            <input type="text" id="nom_athlete" name="nom_athlete" required>

            <label for="prenom_athlete">Prénom :</label>
            <input type="text" id="prenom_athlete" name="prenom_athlete" required>

            <label for="id_genre">Genre :</label>
            <select id="id_genre" name="id_genre" required>
                <option value="">Sélectionner</option>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?php echo htmlspecialchars($genre['id_genre'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($genre['nom_genre'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="id_pays">Pays :</label>
            <select id="id_pays" name="id_pays" required>
                <option value="">Sélectionner un pays</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo htmlspecialchars($country['id_pays'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($country['nom_pays'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Ajouter l'Athlète</button>
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des athlètes</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>