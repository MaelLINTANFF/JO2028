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

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Récupération et filtration des données
    $nomLieu = filter_input(INPUT_POST, 'nomLieu', FILTER_SANITIZE_SPECIAL_CHARS);
    $adresseLieu = filter_input(INPUT_POST, 'adresseLieu', FILTER_SANITIZE_SPECIAL_CHARS);
    $cpLieu = filter_input(INPUT_POST, 'cpLieu', FILTER_SANITIZE_SPECIAL_CHARS);
    $villeLieu = filter_input(INPUT_POST, 'villeLieu', FILTER_SANITIZE_SPECIAL_CHARS);

    // 2. Vérification des champs vides
    if (empty($nomLieu) || empty($adresseLieu) || empty($cpLieu) || empty($villeLieu)) {
        $_SESSION['error'] = "Tous les champs (Nom, Adresse, Code Postal, Ville) sont obligatoires.";
        // Redirection vers le formulaire actuel pour réaffichage des données
        header("Location: add-place.php");
        exit();
    }

    try {
        // 3. Vérification si le lieu existe déjà par son nom
        $queryCheck = "SELECT id_lieu FROM LIEU WHERE nom_lieu = :nomLieu";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Ce lieu existe déjà dans la base de données.";
            header("Location: add-place.php");
            exit();
        }

        // 4. Requête d'insertion
        $query = "INSERT INTO LIEU (nom_lieu, adresse_lieu, cp_lieu, ville_lieu) 
                  VALUES (:nomLieu, :adresseLieu, :cpLieu, :villeLieu)";
        
        $statement = $connexion->prepare($query);
        
        // 5. Liaison des paramètres
        $statement->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
        $statement->bindParam(":adresseLieu", $adresseLieu, PDO::PARAM_STR);
        $statement->bindParam(":cpLieu", $cpLieu, PDO::PARAM_STR);
        $statement->bindParam(":villeLieu", $villeLieu, PDO::PARAM_STR);
        
        // 6. Exécution de la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le lieu **" . htmlspecialchars($nomLieu, ENT_QUOTES, 'UTF-8') . "** a été ajouté avec succès.";
            // Redirection vers la page de gestion des lieux
            header("Location: manage-places.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du lieu.";
            header("Location: add-place.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-place.php");
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
    <title>Ajouter un Lieu - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Ajouter un Lieu</h1>
        
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            // Assurez-vous d'effacer les données du formulaire pour la prochaine tentative
            $nomLieu = $adresseLieu = $cpLieu = $villeLieu = ''; 
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['success']);
        }
        ?>

        <form action="add-place.php" method="post">
            
            <label for="nomLieu">Nom du Lieu :</label>
            <input type="text" name="nomLieu" id="nomLieu" value="<?php echo htmlspecialchars($nomLieu ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="adresseLieu">Adresse :</label>
            <input type="text" name="adresseLieu" id="adresseLieu" value="<?php echo htmlspecialchars($adresseLieu ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="cpLieu">Code Postal :</label>
            <input type="text" name="cpLieu" id="cpLieu" value="<?php echo htmlspecialchars($cpLieu ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="villeLieu">Ville :</label>
            <input type="text" name="villeLieu" id="villeLieu" value="<?php echo htmlspecialchars($villeLieu ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <input type="submit" value="Ajouter le Lieu">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-places.php">Retour à la gestion des lieux</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>

</html>