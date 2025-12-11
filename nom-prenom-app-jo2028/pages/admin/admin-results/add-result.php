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

// 1. Récupération des listes d'épreuves et d'athlètes
$events = [];
$athletes = [];
try {
    // Récupérer toutes les épreuves, ordonnées par date
    $query_events = "SELECT id_epreuve, nom_epreuve, date_epreuve FROM EPREUVE ORDER BY date_epreuve DESC, nom_epreuve";
    $statement_events = $connexion->query($query_events);
    $events = $statement_events->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les athlètes, avec leur pays
    $query_athletes = "
        SELECT A.id_athlete, A.nom_athlete, A.prenom_athlete, P.nom_pays 
        FROM ATHLETE A
        INNER JOIN PAYS P ON A.id_pays = P.id_pays
        ORDER BY A.nom_athlete, A.prenom_athlete
    ";
    $statement_athletes = $connexion->query($query_athletes);
    $athletes = $statement_athletes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors du chargement des listes : " . $e->getMessage();
    header("Location: manage-results.php");
    exit();
}


// 2. Traitement du formulaire d'ajout (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $_SESSION['error'] = "Erreur de sécurité. Le formulaire n'est pas valide.";
        header("Location: add-result.php");
        exit();
    }
    
    // Récupération et validation des données du formulaire
    $id_epreuve = filter_input(INPUT_POST, 'id_epreuve', FILTER_VALIDATE_INT);
    $id_athlete = filter_input(INPUT_POST, 'id_athlete', FILTER_VALIDATE_INT);
    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);

    // Vérification minimale des champs requis
    if ($id_epreuve === false || $id_athlete === false || empty($resultat)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires et doivent être valides.";
        header("Location: add-result.php");
        exit();
    }

    try {
        // Requête d'insertion dans la table PARTICIPER (qui stocke les résultats)
        // Note : C'est une insertion car nous ajoutons un nouveau résultat pour un athlète à une épreuve.
        $sql = "INSERT INTO PARTICIPER (id_epreuve, id_athlete, resultat) 
                VALUES (:id_epreuve, :id_athlete, :resultat)";
        
        $statement = $connexion->prepare($sql);

        // Liaison des paramètres
        $statement->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
        $statement->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
        $statement->bindParam(':resultat', $resultat);

        $statement->execute();

        // Message de succès et redirection
        $_SESSION['success'] = "Le résultat a été enregistré avec succès.";
        header("Location: manage-results.php");
        exit();

    } catch (PDOException $e) {
        // Si la clé composite existe déjà (l'athlète a déjà un résultat pour cette épreuve)
        if ($e->getCode() == '23000') { 
            $_SESSION['error'] = "Cet athlète a déjà un résultat enregistré pour cette épreuve. Veuillez le modifier au lieu de l'ajouter.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du résultat : " . $e->getMessage();
        }
        header("Location: add-result.php");
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
    <title>Ajouter un Résultat - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Ajouter un Résultat</h1>

        <?php
        // Afficher les messages d'erreur s'ils existent
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="add-result.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <label for="id_epreuve">Épreuve :</label>
            <select id="id_epreuve" name="id_epreuve" required>
                <option value="">Sélectionner une épreuve</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo htmlspecialchars($event['id_epreuve'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php 
                            // Formatage de l'affichage : Nom de l'épreuve (Date)
                            $date_formattee = date("d/m/Y", strtotime($event['date_epreuve']));
                            echo htmlspecialchars($event['nom_epreuve'] . " (" . $date_formattee . ")", ENT_QUOTES, 'UTF-8'); 
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_athlete">Athlète :</label>
            <select id="id_athlete" name="id_athlete" required>
                <option value="">Sélectionner un athlète</option>
                <?php foreach ($athletes as $athlete): ?>
                    <option value="<?php echo htmlspecialchars($athlete['id_athlete'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php 
                            // Formatage de l'affichage : Prénom Nom (Pays)
                            echo htmlspecialchars($athlete['prenom_athlete'] . " " . $athlete['nom_athlete'] . " (" . $athlete['nom_pays'] . ")", ENT_QUOTES, 'UTF-8'); 
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="resultat">Résultat (Performance, Médaille, Temps, etc.) :</label>
            <input type="text" id="resultat" name="resultat" required maxlength="100">

            <button type="submit">Enregistrer le Résultat</button>
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-results.php">Retour à la gestion des résultats</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>