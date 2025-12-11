<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'athlète est fourni
if (!isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "ID de l'athlète manquant.";
    header("Location: manage-athletes.php");
    exit();
}

$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si l'ID est valide
if (!$id_athlete) {
    $_SESSION['error'] = "ID de l'athlète invalide.";
    header("Location: manage-athletes.php");
    exit();
}

// 1. Récupération des listes de pays et de genres pour les menus déroulants
$countries = [];
$genres = [];
try {
    // Récupérer tous les pays
    $query_countries = "SELECT id_pays, nom_pays FROM PAYS ORDER BY nom_pays";
    $statement_countries = $connexion->query($query_countries);
    $countries = $statement_countries->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les genres/sexes
    $query_genres = "SELECT id_genre, nom_genre FROM GENRE ORDER BY nom_genre";
    $statement_genres = $connexion->query($query_genres);
    $genres = $statement_genres->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors du chargement des listes : " . $e->getMessage();
    header("Location: manage-athletes.php");
    exit();
}

// 2. Récupération des données de l'athlète actuel (pour pré-remplissage)
try {
    // La requête est simplifiée (SANS date_naissance_athlete)
    $queryAthlete = "SELECT id_athlete, nom_athlete, prenom_athlete, id_genre, id_pays FROM ATHLETE WHERE id_athlete = :id_athlete";
    $statementAthlete = $connexion->prepare($queryAthlete);
    $statementAthlete->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
    $statementAthlete->execute();

    if ($statementAthlete->rowCount() > 0) {
        $athlete = $statementAthlete->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Athlète non trouvé(e).";
        header("Location: manage-athletes.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-athletes.php");
    exit();
}


// 3. Traitement du formulaire de modification (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération et validation des données du formulaire (SANS date de naissance)
    $nom_athlete = filter_input(INPUT_POST, 'nom_athlete', FILTER_SANITIZE_STRING);
    $prenom_athlete = filter_input(INPUT_POST, 'prenom_athlete', FILTER_SANITIZE_STRING);
    $id_genre = filter_input(INPUT_POST, 'id_genre', FILTER_VALIDATE_INT); // CORRIGÉ
    $id_pays = filter_input(INPUT_POST, 'id_pays', FILTER_VALIDATE_INT);

    // Vérification minimale des champs requis
    if (empty($nom_athlete) || empty($prenom_athlete) || $id_genre === false || $id_pays === false) {
        $_SESSION['error'] = "Tous les champs sont obligatoires et doivent être valides.";
        header("Location: modify-athlete.php?id_athlete=$id_athlete");
        exit();
    }

    try {
        // Requête de mise à jour dans la table ATHLETE (SANS date_naissance_athlete, CORRIGÉE pour id_genre)
        $sql = "UPDATE ATHLETE SET 
                    nom_athlete = :nom_athlete, 
                    prenom_athlete = :prenom_athlete, 
                    id_genre = :id_genre, 
                    id_pays = :id_pays
                WHERE id_athlete = :id_athlete";
        
        $statement = $connexion->prepare($sql);

        // Liaison des paramètres
        $statement->bindParam(':nom_athlete', $nom_athlete);
        $statement->bindParam(':prenom_athlete', $prenom_athlete);
        $statement->bindParam(':id_genre', $id_genre, PDO::PARAM_INT); // Utilisation de l'ID
        $statement->bindParam(':id_pays', $id_pays, PDO::PARAM_INT);
        $statement->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);

        $statement->execute();

        // Message de succès et redirection
        $_SESSION['success'] = "L'athlète " . htmlspecialchars($prenom_athlete . " " . $nom_athlete) . " a été modifié(e) avec succès.";
        header("Location: manage-athletes.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la modification de l'athlète : " . $e->getMessage();
        header("Location: modify-athlete.php?id_athlete=$id_athlete");
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
    <title>Modifier un Athlète - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Modifier l'Athlète : <?php echo htmlspecialchars($athlete['prenom_athlete'] . " " . $athlete['nom_athlete']); ?></h1>

        <?php
        // Afficher les messages d'erreur s'ils existent
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="modify-athlete.php?id_athlete=<?php echo $id_athlete; ?>" method="post">
            
            <label for="nom_athlete">Nom :</label>
            <input type="text" id="nom_athlete" name="nom_athlete" 
                   value="<?php echo htmlspecialchars($athlete['nom_athlete'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="prenom_athlete">Prénom :</label>
            <input type="text" id="prenom_athlete" name="prenom_athlete" 
                   value="<?php echo htmlspecialchars($athlete['prenom_athlete'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="id_genre">Genre :</label>
            <select id="id_genre" name="id_genre" required>
                <option value="">Sélectionner</option>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?php echo htmlspecialchars($genre['id_genre'], ENT_QUOTES, 'UTF-8'); ?>"
                        <?php echo ($genre['id_genre'] == $athlete['id_genre']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($genre['nom_genre'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="id_pays">Pays :</label>
            <select id="id_pays" name="id_pays" required>
                <option value="">Sélectionner un pays</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo htmlspecialchars($country['id_pays'], ENT_QUOTES, 'UTF-8'); ?>"
                        <?php echo ($country['id_pays'] == $athlete['id_pays']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($country['nom_pays'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Modifier l'Athlète</button>
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