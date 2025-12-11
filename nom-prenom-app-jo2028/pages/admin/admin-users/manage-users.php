<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté et qu'il s'agit bien d'un administrateur
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Générer un token CSRF si ce n'est pas déjà fait
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
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
    <title>Gestion des Administrateurs - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Liste des Administrateurs</h1>
        <div class="action-buttons">
            <button onclick="openAddAdminForm()">Ajouter un Administrateur</button>
        </div>
        
        <?php
        // Afficher les messages de succès ou d'erreur
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }

        try {
            // Sélection des administrateurs (sauf le mot de passe)
            $query = "SELECT id_admin, nom_admin, prenom_admin, login FROM ADMINISTRATEUR ORDER BY nom_admin, prenom_admin";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des administrateurs
            if ($statement->rowCount() > 0) {
                echo "<table>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Login</th>
                            <th>Modifier</th>
                            <th>Supprimer</th>
                        </tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $id_admin = $row['id_admin']; 
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($id_admin, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_admin'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['prenom_admin'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['login'], ENT_QUOTES, 'UTF-8') . "</td>";
                    
                    echo "<td><button onclick='openModifyAdminForm($id_admin)'>Modifier</button></td>";
                    echo "<td><button onclick='deleteAdminConfirmation($id_admin)'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun administrateur n'a été trouvé.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : Impossible de charger la liste des administrateurs. " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
        ?>
        
        <p class="paragraph-link">
            <a class="link-home" href="../admin.php">Accueil administration</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

    <script>
        function openAddAdminForm() {
            window.location.href = 'add-user.php'; // Utilisez 'add-user.php'
        }

        function openModifyAdminForm(id_admin) {
            window.location.href = 'modify-user.php?id_admin=' + id_admin; // Utilisez 'modify-user.php'
        }

        function deleteAdminConfirmation(id_admin) {
            // Avertissement : Vérifiez que ce n'est pas le seul admin !
            if (confirm("Êtes-vous sûr de vouloir supprimer cet administrateur ?")) {
                window.location.href = 'delete-user.php?id_admin=' + id_admin; // Utilisez 'delete-user.php'
            }
        }
    </script>
</body>

</html>