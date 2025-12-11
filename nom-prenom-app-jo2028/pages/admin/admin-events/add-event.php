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

// 1. Récupération des listes de sports et de lieux pour les menus déroulants
$sports = [];
$places = [];
try {
    // Récupérer tous les sports
    $query_sports = "SELECT id_sport, nom_sport FROM SPORT ORDER BY nom_sport";
    $statement_sports = $connexion->query($query_sports);
    $sports = $statement_sports->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les lieux
    $query_places = "SELECT id_lieu, nom_lieu FROM LIEU ORDER BY nom_lieu";
    $statement_places = $connexion->query($query_places);
    $places = $statement_places->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors du chargement des listes : " . $e->getMessage();
    header("Location: manage-events.php");
    exit();
}


// 2. Traitement du formulaire d'ajout (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $_SESSION['error'] = "Erreur de sécurité. Le formulaire n'est pas valide.";
        header("Location: add-event.php");
        exit();
    }
    
    // Récupération et validation des données du formulaire (SANS description)
    $nom_epreuve = filter_input(INPUT_POST, 'nom_epreuve', FILTER_SANITIZE_STRING);
    $date_epreuve = filter_input(INPUT_POST, 'date_epreuve', FILTER_SANITIZE_STRING); // Format YYYY-MM-DD
    $heure_epreuve = filter_input(INPUT_POST, 'heure_epreuve', FILTER_SANITIZE_STRING); // Format HH:MM:SS
    $id_sport = filter_input(INPUT_POST, 'id_sport', FILTER_VALIDATE_INT);
    $id_lieu = filter_input(INPUT_POST, 'id_lieu', FILTER_VALIDATE_INT);

    // Vérification minimale des champs requis
    if (empty($nom_epreuve) || empty($date_epreuve) || empty($heure_epreuve) || $id_sport === false || $id_lieu === false) {
        $_SESSION['error'] = "Tous les champs obligatoires (Nom, Date, Heure, Sport, Lieu) doivent être remplis ou valides.";
        header("Location: add-event.php");
        exit();
    }

    try {
        // Requête d'insertion dans la table EPREUVE (SANS description_epreuve)
        $sql = "INSERT INTO EPREUVE (nom_epreuve, date_epreuve, heure_epreuve, id_sport, id_lieu) 
                VALUES (:nom_epreuve, :date_epreuve, :heure_epreuve, :id_sport, :id_lieu)";
        
        $statement = $connexion->prepare($sql);

        // Liaison des paramètres (SANS description_epreuve)
        $statement->bindParam(':nom_epreuve', $nom_epreuve);
        $statement->bindParam(':date_epreuve', $date_epreuve);
        $statement->bindParam(':heure_epreuve', $heure_epreuve);
        $statement->bindParam(':id_sport', $id_sport, PDO::PARAM_INT);
        $statement->bindParam(':id_lieu', $id_lieu, PDO::PARAM_INT);

        $statement->execute();

        // Message de succès et redirection
        $_SESSION['success'] = "L'épreuve **" . htmlspecialchars($nom_epreuve) . "** a été ajoutée avec succès au calendrier.";
        header("Location: manage-events.php");
        exit();

    } catch (PDOException $e) {
        // En cas d'erreur (doublon, clé étrangère manquante, etc.)
        $_SESSION['error'] = "Erreur lors de l'ajout de l'épreuve : " . $e->getMessage();
        header("Location: add-event.php");
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
    <title>Ajouter une Épreuve - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Ajouter une Épreuve</h1>

        <?php
        // Afficher les messages d'erreur s'ils existent
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="add-event.php" method="post" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <label for="nom_epreuve">Nom de l'épreuve :</label>
            <input type="text" id="nom_epreuve" name="nom_epreuve" required>

            <label for="id_sport">Sport :</label>
            <select id="id_sport" name="id_sport" required>
                <option value="">Sélectionner un sport</option>
                <?php foreach ($sports as $sport): ?>
                    <option value="<?php echo htmlspecialchars($sport['id_sport'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($sport['nom_sport'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="id_lieu">Lieu :</label>
            <select id="id_lieu" name="id_lieu" required>
                <option value="">Sélectionner un lieu</option>
                <?php foreach ($places as $place): ?>
                    <option value="<?php echo htmlspecialchars($place['id_lieu'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($place['nom_lieu'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="date_epreuve">Date de l'épreuve :</label>
            <input type="date" id="date_epreuve" name="date_epreuve" required>

            <label for="heure_epreuve">Heure de l'épreuve :</label>
            <input type="time" id="heure_epreuve" name="heure_epreuve" step="1" required>

            <button type="submit">Ajouter l'Épreuve</button>
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

    <script>
        function validateForm() {
            const nom_epreuve = document.getElementById('nom_epreuve').value;
            const id_sport = document.getElementById('id_sport').value;
            const id_lieu = document.getElementById('id_lieu').value;

            if (nom_epreuve.trim() === "" || id_sport === "" || id_lieu === "") {
                alert("Veuillez remplir tous les champs obligatoires (Nom, Sport, Lieu).");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>