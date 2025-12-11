<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Générer un token CSRF
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
    <title>Gestion des Genres - Jeux Olympiques - Los Angeles 2028</title>
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
                <li><a href="../admin-genres/manage-genres.php">Gestion Genres</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Liste des Genres</h1>
        <div class="action-buttons">
            <button onclick="openAddGenreForm()">Ajouter un Genre</button>
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
            $query = "SELECT id_genre, nom_genre FROM GENRE ORDER BY nom_genre";
            $statement = $connexion->prepare($query);
            $statement->execute();

            if ($statement->rowCount() > 0) {
                echo "<table>
                        <tr>
                            <th>ID</th>
                            <th>Nom du Genre</th>
                            <th>Modifier</th>
                            <th>Supprimer</th>
                        </tr>";

                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $id_genre = $row['id_genre']; 
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($id_genre, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_genre'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td><button onclick='openModifyGenreForm($id_genre)'>Modifier</button></td>";
                    echo "<td><button onclick='deleteGenreConfirmation($id_genre)'>Supprimer</button></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucun genre trouvé dans la base de données. Ajoutez le genre 'Homme' et 'Femme' par défaut.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : Impossible de charger la liste des genres. " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
        function openAddGenreForm() {
            window.location.href = 'add-genre.php';
        }

        function openModifyGenreForm(id_genre) {
            window.location.href = 'modify-genre.php?id_genre=' + id_genre;
        }

        function deleteGenreConfirmation(id_genre) {
            // Avertissement : Supprimer un genre peut invalider des athlètes !
            if (confirm("ATTENTION : Êtes-vous sûr de vouloir supprimer ce genre? Les athlètes liés à ce genre deviendront invalides.")) {
                window.location.href = 'delete-genre.php?id_genre=' + id_genre;
            }
        }
    </script>
</body>
</html>