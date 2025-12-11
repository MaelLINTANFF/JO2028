<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si les deux IDs sont fournis
if (!isset($_GET['id_epreuve']) || !isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "Paramètres de résultat manquants.";
    header("Location: manage-results.php");
    exit();
}

$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);
$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si les IDs sont valides
if ($id_epreuve === false || $id_athlete === false) {
    $_SESSION['error'] = "IDs de résultat invalides.";
    header("Location: manage-results.php");
    exit();
}

// 1. Récupération des données du résultat actuel (pour pré-remplissage)
try {
    // Jointure pour obtenir les noms complets
    $queryResult = "
        SELECT 
            P.resultat, 
            E.nom_epreuve, 
            E.date_epreuve,
            A.nom_athlete, 
            A.prenom_athlete
        FROM 
            PARTICIPER P
        INNER JOIN 
            EPREUVE E ON P.id_epreuve = E.id_epreuve
        INNER JOIN 
            ATHLETE A ON P.id_athlete = A.id_athlete
        WHERE 
            P.id_epreuve = :id_epreuve AND P.id_athlete = :id_athlete
    ";
    $statementResult = $connexion->prepare($queryResult);
    $statementResult->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);
    $statementResult->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
    $statementResult->execute();

    if ($statementResult->rowCount() > 0) {
        $resultData = $statementResult->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Résultat non trouvé.";
        header("Location: manage-results.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données lors du chargement : " . $e->getMessage();
    header("Location: manage-results.php");
    exit();
}


// 2. Traitement du formulaire de modification (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Le token CSRF est une bonne pratique, non ajouté ici pour la brièveté si non demandé
    
    // Récupération et validation du nouveau résultat
    $nouveau_resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);

    // Vérification minimale des champs requis
    if (empty($nouveau_resultat)) {
        $_SESSION['error'] = "Le champ résultat ne peut pas être vide.";
        header("Location: modify-result.php?id_epreuve=$id_epreuve&id_athlete=$id_athlete");
        exit();
    }

    try {
        // Requête de mise à jour dans la table PARTICIPER
        $sql = "UPDATE PARTICIPER SET resultat = :resultat
                WHERE id_epreuve = :id_epreuve AND id_athlete = :id_athlete";
        
        $statement = $connexion->prepare($sql);

        // Liaison des paramètres
        $statement->bindParam(':resultat', $nouveau_resultat);
        $statement->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
        $statement->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);

        $statement->execute();

        // Message de succès et redirection
        $_SESSION['success'] = "Le résultat pour l'athlète **" . htmlspecialchars($resultData['prenom_athlete'] . " " . $resultData['nom_athlete']) . "** à l'épreuve **" . htmlspecialchars($resultData['nom_epreuve']) . "** a été modifié avec succès.";
        header("Location: manage-results.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la modification du résultat : " . $e->getMessage();
        header("Location: modify-result.php?id_epreuve=$id_epreuve&id_athlete=$id_athlete");
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
    <title>Modifier un Résultat - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Modifier le Résultat</h1>
        <h2>
            Épreuve : <?php echo htmlspecialchars($resultData['nom_epreuve']); ?><br>
            Athlète : <?php echo htmlspecialchars($resultData['prenom_athlete'] . " " . $resultData['nom_athlete']); ?>
        </h2>

        <?php
        // Afficher les messages d'erreur s'ils existent
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="modify-result.php?id_epreuve=<?php echo $id_epreuve; ?>&id_athlete=<?php echo $id_athlete; ?>" method="post">
            
            <label for="resultat">Nouveau Résultat :</label>
            <input type="text" id="resultat" name="resultat" 
                   value="<?php echo htmlspecialchars($resultData['resultat'], ENT_QUOTES, 'UTF-8'); ?>" required maxlength="100">

            <button type="submit">Modifier le Résultat</button>
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