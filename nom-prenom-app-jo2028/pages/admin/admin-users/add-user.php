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
        header("Location: add-user.php");
        exit();
    }
    
    // Récupération et validation des données du formulaire
    $nom_admin = filter_input(INPUT_POST, 'nom_admin', FILTER_SANITIZE_STRING);
    $prenom_admin = filter_input(INPUT_POST, 'prenom_admin', FILTER_SANITIZE_STRING);
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);

    // Vérification minimale des champs requis
    if (empty($nom_admin) || empty($prenom_admin) || empty($login) || empty($password)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header("Location: add-user.php");
        exit();
    }

    try {
        // HACHAGE DU MOT DE PASSE : ESSENTIEL POUR LA SÉCURITÉ
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Requête d'insertion
        $sql = "INSERT INTO ADMINISTRATEUR (nom_admin, prenom_admin, login, password) VALUES (:nom_admin, :prenom_admin, :login, :password)";
        
        $statement = $connexion->prepare($sql);

        // Liaison des paramètres
        $statement->bindParam(':nom_admin', $nom_admin);
        $statement->bindParam(':prenom_admin', $prenom_admin);
        $statement->bindParam(':login', $login);
        $statement->bindParam(':password', $hashed_password);

        $statement->execute();

        // Message de succès et redirection
        $_SESSION['success'] = "L'administrateur a été ajouté avec succès.";
        header("Location: manage-users.php");
        exit();

    } catch (PDOException $e) {
        // En cas de doublon (si login est unique)
        if ($e->getCode() == '23000') { 
            $_SESSION['error'] = "Ce login est déjà utilisé par un autre administrateur.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'administrateur : " . $e->getMessage();
        }
        header("Location: add-user.php");
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
    <title>Ajouter un Administrateur - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Ajouter un Administrateur</h1>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="add-user.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <label for="nom_admin">Nom :</label>
            <input type="text" id="nom_admin" name="nom_admin" required>
            
            <label for="prenom_admin">Prénom :</label>
            <input type="text" id="prenom_admin" name="prenom_admin" required>

            <label for="login">Login :</label>
            <input type="text" id="login" name="login" required>
            
            <label for="password">Mot de Passe :</label>
            <input type="password" id="password" name="password" required>

            <p class="warning-text">Le mot de passe sera automatiquement **haché** lors de l'enregistrement.</p>

            <button type="submit">Ajouter l'Administrateur</button>
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