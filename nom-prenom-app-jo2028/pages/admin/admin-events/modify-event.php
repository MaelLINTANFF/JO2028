<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'épreuve est fourni
if (!isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'épreuve manquant.";
    header("Location: manage-events.php");
    exit();
}

$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

// Vérifiez si l'ID est valide
if (!$id_epreuve) {
    $_SESSION['error'] = "ID de l'épreuve invalide.";
    header("Location: manage-events.php");
    exit();
}

// 1. Récupération des listes de sports et de lieux (pour le formulaire)
$sports = [];
$places = [];
try {
    $query_sports = "SELECT id_sport, nom_sport FROM SPORT ORDER BY nom_sport";
    $statement_sports = $connexion->query($query_sports);
    $sports = $statement_sports->fetchAll(PDO::FETCH_ASSOC);

    $query_places = "SELECT id_lieu, nom_lieu FROM LIEU ORDER BY nom_lieu";
    $statement_places = $connexion->query($query_places);
    $places = $statement_places->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors du chargement des listes de sports/lieux : " . $e->getMessage();
    header("Location: manage-events.php");
    exit();
}

// 2. Récupération des données de l'épreuve actuelle (SANS description)
try {
    // La colonne description_epreuve est retirée du SELECT
    $queryEvent = "SELECT id_epreuve, nom_epreuve, date_epreuve, heure_epreuve, id_sport, id_lieu FROM EPREUVE WHERE id_epreuve = :id_epreuve";
    $statementEvent = $connexion->prepare($queryEvent);
    $statementEvent->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);
    $statementEvent->execute();

    if ($statementEvent->rowCount() > 0) {
        $event = $statementEvent->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Épreuve non trouvée.";
        header("Location: manage-events.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-events.php");
    exit();
}


// 3. Traitement du formulaire de modification (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération et validation des données du formulaire (description_epreuve n'est plus récupérée)
    $nom_epreuve = filter_input(INPUT_POST, 'nom_epreuve', FILTER_SANITIZE_STRING);
    $date_epreuve = filter_input(INPUT_POST, 'date_epreuve', FILTER_SANITIZE_STRING); // Format YYYY-MM-DD
    $heure_epreuve = filter_input(INPUT_POST, 'heure_epreuve', FILTER_SANITIZE_STRING); // Format HH:MM:SS
    $id_sport = filter_input(INPUT_POST, 'id_sport', FILTER_VALIDATE_INT);
    $id_lieu = filter_input(INPUT_POST, 'id_lieu', FILTER_VALIDATE_INT);

    // Vérification minimale des champs requis
    if (empty($nom_epreuve) || empty($date_epreuve) || empty($heure_epreuve) || $id_sport === false || $id_lieu === false) {
        $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis ou valides.";
        header("Location: modify-event.php?id_epreuve=$id_epreuve");
        exit();
    }

    try {
        // Requête de mise à jour dans la table EPREUVE (description_epreuve est retirée de l'UPDATE)
        $sql = "UPDATE EPREUVE SET 
                    nom_epreuve = :nom_epreuve, 
                    date_epreuve = :date_epreuve, 
                    heure_epreuve = :heure_epreuve, 
                    id_sport = :id_sport, 
                    id_lieu = :id_lieu
                WHERE id_epreuve = :id_epreuve";
        
        $statement = $connexion->prepare($sql);

        // Liaison des paramètres (description_epreuve n'est plus liée)
        $statement->bindParam(':nom_epreuve', $nom_epreuve);
        $statement->bindParam(':date_epreuve', $date_epreuve);
        $statement->bindParam(':heure_epreuve', $heure_epreuve);
        $statement->bindParam(':id_sport', $id_sport, PDO::PARAM_INT);
        $statement->bindParam(':id_lieu', $id_lieu, PDO::PARAM_INT);
        $statement->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);

        $statement->execute();

        // Message de succès et redirection
        $_SESSION['success'] = "L'épreuve '{$nom_epreuve}' a été modifiée avec succès.";
        header("Location: manage-events.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la modification de l'épreuve : " . $e->getMessage();
        header("Location: modify-event.php?id_epreuve=$id_epreuve");
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
    <title>Modifier une Épreuve - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Modifier l'Épreuve : <?php echo htmlspecialchars($event['nom_epreuve']); ?></h1>

        <?php
        // Afficher les messages d'erreur s'ils existent
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="modify-event.php?id_epreuve=<?php echo $id_epreuve; ?>" method="post" onsubmit="return validateForm()">
            
            <label for="nom_epreuve">Nom de l'épreuve :</label>
            <input type="text" id="nom_epreuve" name="nom_epreuve" 
                   value="<?php echo htmlspecialchars($event['nom_epreuve'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="id_sport">Sport :</label>
            <select id="id_sport" name="id_sport" required>
                <option value="">Sélectionner un sport</option>
                <?php foreach ($sports as $sport): ?>
                    <option value="<?php echo htmlspecialchars($sport['id_sport'], ENT_QUOTES, 'UTF-8'); ?>"
                        <?php echo ($sport['id_sport'] == $event['id_sport']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sport['nom_sport'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="id_lieu">Lieu :</label>
            <select id="id_lieu" name="id_lieu" required>
                <option value="">Sélectionner un lieu</option>
                <?php foreach ($places as $place): ?>
                    <option value="<?php echo htmlspecialchars($place['id_lieu'], ENT_QUOTES, 'UTF-8'); ?>"
                        <?php echo ($place['id_lieu'] == $event['id_lieu']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($place['nom_lieu'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="date_epreuve">Date de l'épreuve :</label>
            <input type="date" id="date_epreuve" name="date_epreuve" 
                   value="<?php echo htmlspecialchars($event['date_epreuve'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="heure_epreuve">Heure de l'épreuve :</label>
            <input type="time" id="heure_epreuve" name="heure_epreuve" step="1" 
                   value="<?php echo htmlspecialchars(substr($event['heure_epreuve'], 0, 8), ENT_QUOTES, 'UTF-8'); ?>" required>

            <button type="submit">Modifier l'Épreuve</button>
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion du calendrier</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>