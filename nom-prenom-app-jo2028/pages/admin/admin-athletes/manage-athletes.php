<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
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
    <title>Gestion des Athlètes - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Liste des Athlètes</h1>
        <div class="action-buttons">
            <button onclick="openAddAthleteForm()">Ajouter un Athlète</button>
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
            // Requête corrigée : utilise id_genre et joint la table GENRE pour obtenir le nom
            $query = "
                SELECT 
                    A.id_athlete,
                    A.nom_athlete,
                    A.prenom_athlete,
                    P.nom_pays,
                    G.nom_genre
                FROM 
                    ATHLETE A
                INNER JOIN 
                    PAYS P ON A.id_pays = P.id_pays
                INNER JOIN
                    GENRE G ON A.id_genre = G.id_genre 
                ORDER BY 
                    A.nom_athlete, A.prenom_athlete;
            ";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des athlètes
            if ($statement->rowCount() > 0) {
                echo "<table>
                        <tr>
                            <th>Nom & Prénom</th>
                            <th>Pays</th>
                            <th>Genre</th>
                            <th>Modifier</th>
                            <th>Supprimer</th>
                        </tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $id_athlete = $row['id_athlete']; 
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_athlete'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($row['prenom_athlete'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_pays'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_genre'], ENT_QUOTES, 'UTF-8') . "</td>"; // Affiche le nom du genre
                    echo "<td><button onclick='openModifyAthleteForm($id_athlete)'>Modifier</button></td>";
                    echo "<td><button onclick='deleteAthleteConfirmation($id_athlete)'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun athlète trouvé dans la base de données.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : Impossible de charger la liste des athlètes. " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
        function openAddAthleteForm() {
            window.location.href = 'add-athlete.php';
        }

        function openModifyAthleteForm(id_athlete) {
            window.location.href = 'modify-athlete.php?id_athlete=' + id_athlete;
        }

        function deleteAthleteConfirmation(id_athlete) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cet athlète? Cela supprimera également ses résultats associés!")) {
                window.location.href = 'delete-athlete.php?id_athlete=' + id_athlete;
            }
        }
    </script>
</body>

</html>