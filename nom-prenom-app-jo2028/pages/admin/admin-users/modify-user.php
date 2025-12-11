<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID est fourni
if (!isset($_GET['id_admin'])) {
    $_SESSION['error'] = "ID d'administrateur manquant.";
    header("Location: manage-users.php");
    exit();
}

$id_admin = filter_input(INPUT_GET, 'id_admin', FILTER_VALIDATE_INT);

// Vérifiez si l'ID est valide
if ($id_admin === false) {
    $_SESSION['error'] = "ID d'administrateur invalide.";
    header("Location: manage-users.php");
    exit();
}

// 1. Récupération des données actuelles de l'administrateur (pour pré-remplissage)
try {
    // On ne récupère JAMAIS le mot de passe actuel
    $queryAdmin = "SELECT nom_admin, prenom_admin, login FROM ADMINISTRATEUR WHERE id_admin = :id_admin";
    $statementAdmin = $connexion->prepare($queryAdmin);
    $statementAdmin->bindParam(":id_admin", $id_admin, PDO::PARAM_INT);
    $statementAdmin->execute();

    if ($statementAdmin->rowCount() > 0) {
        $admin = $statementAdmin->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Administrateur non trouvé.";
        header("Location: manage-users.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données lors du chargement : " . $e->getMessage();
    header("Location: manage-users.php");
    exit();
}

// 2. Traitement du formulaire de modification (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération des données du formulaire
    $nom_admin = filter_input(INPUT_POST, 'nom_admin', FILTER_SANITIZE_STRING);
    $prenom_admin = filter_input(INPUT_POST, 'prenom_admin', FILTER_SANITIZE_STRING);
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT); // Récupération brute

    // Vérification minimale
    if (empty($nom_admin) || empty($prenom_admin) || empty($login)) {
        $_SESSION['error'] = "Tous les champs (sauf le mot de passe) sont obligatoires.";
        header("Location: modify-user.php?id_admin=$id_admin");
        exit();
    }

    try {
        // Début de la requête SQL d'UPDATE
        $sql = "UPDATE ADMINISTRATEUR SET nom_admin = :nom_admin, prenom_admin = :prenom_admin, login = :login";
        $params = [
            ':nom_admin' => $nom_admin,
            ':prenom_admin' => $prenom_admin,
            ':login' => $login,
            ':id_admin' => $id_admin
        ];
        
        // Ajout du mot de passe HACHÉ si le champ n'est pas vide
        if (!empty($password)) {
            // HACHAGE DU MOT DE PASSE : ESSENTIEL POUR LA SÉCURITÉ
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
            $params[':password'] = $hashed_password;
        }

        $sql .= " WHERE id_admin = :id_admin";
        
        $statement = $connexion->prepare($sql);

        // Exécution de la requête avec les paramètres dynamiques
        $statement->execute($params);

        // Si l'administrateur modifie son propre login, on met à jour la session
        if ($id_admin == $_SESSION['id_admin']) {
            $_SESSION['login'] = $login;
            $_SESSION['nom_admin'] = $nom_admin;
            $_SESSION['prenom_admin'] = $prenom_admin;
        }

        // Message de succès et redirection
        $_SESSION['success'] = "L'administrateur a été modifié avec succès.";
        header("Location: manage-users.php");
        exit();

    } catch (PDOException $e) {
         // Code 23000 = Contrainte d'unicité (ex: login déjà existant)
         if ($e->getCode() == '23000') { 
            $_SESSION['error'] = "Ce login est déjà utilisé par un autre administrateur.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'administrateur : " . $e->getMessage();
        }
        header("Location: modify-user.php?id_admin=$id_admin");
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
    <title>Modifier un Administrateur - Jeux Olympiques - Los Angeles 2028</title>
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
                <li><a href="../admin-genres/manage-genres.php">Gestion Genre</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Modifier l'Administrateur</h1>
        <h2>Admin : <?php echo htmlspecialchars($admin['prenom_admin'] . " " . $admin['nom_admin']); ?></h2>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="modify-user.php?id_admin=<?php echo $id_admin; ?>" method="post">
            
            <label for="nom_admin">Nom :</label>
            <input type="text" id="nom_admin" name="nom_admin" 
                   value="<?php echo htmlspecialchars($admin['nom_admin'], ENT_QUOTES, 'UTF-8'); ?>" required>
            
            <label for="prenom_admin">Prénom :</label>
            <input type="text" id="prenom_admin" name="prenom_admin" 
                   value="<?php echo htmlspecialchars($admin['prenom_admin'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="login">Login :</label>
            <input type="text" id="login" name="login" 
                   value="<?php echo htmlspecialchars($admin['login'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="password">Nouveau Mot de Passe (laisser vide pour ne pas changer) :</label>
            <input type="password" id="password" name="password">

            <button type="submit">Modifier l'Administrateur</button>
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-users.php">Retour à la gestion des administrateurs</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>