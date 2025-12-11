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
    <title>Gestion des Résultats - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Liste des Résultats par Épreuve</h1>
        <div class="action-buttons">
            <button onclick="openAddResultForm()">Ajouter un Résultat</button>
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
            // Requête pour récupérer les résultats avec les noms des épreuves et des athlètes
            $query = "
                SELECT 
                    P.id_epreuve,
                    P.id_athlete,
                    P.resultat,
                    E.nom_epreuve,
                    A.nom_athlete,
                    A.prenom_athlete
                FROM 
                    PARTICIPER P
                INNER JOIN 
                    EPREUVE E ON P.id_epreuve = E.id_epreuve
                INNER JOIN 
                    ATHLETE A ON P.id_athlete = A.id_athlete
                ORDER BY 
                    E.date_epreuve DESC, E.heure_epreuve, E.nom_epreuve, P.resultat ASC;
            ";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table>
                        <tr>
                            <th>Épreuve</th>
                            <th>Athlète</th>
                            <th>Résultat (Performance/Médaille)</th>
                            <th>Modifier</th>
                            <th>Supprimer</th>
                        </tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $id_epreuve = $row['id_epreuve']; 
                    $id_athlete = $row['id_athlete']; 
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['prenom_athlete'] . " " . $row['nom_athlete'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['resultat'], ENT_QUOTES, 'UTF-8') . "</td>";
                    
                    // Les boutons nécessitent de passer la clé composite
                    echo "<td><button onclick='openModifyResultForm(\"$id_epreuve\", \"$id_athlete\")'>Modifier</button></td>";
                    echo "<td><button onclick='deleteResultConfirmation(\"$id_epreuve\", \"$id_athlete\")'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun résultat n'a été enregistré pour le moment.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : Impossible de charger la liste des résultats. " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
        function openAddResultForm() {
            // Pointez vers la page d'ajout d'un résultat
            window.location.href = 'add-result.php';
        }

        function openModifyResultForm(id_epreuve, id_athlete) {
            // Pointez vers la page de modification en passant les deux IDs (clé composite)
            window.location.href = 'modify-result.php?id_epreuve=' + id_epreuve + '&id_athlete=' + id_athlete;
        }

        function deleteResultConfirmation(id_epreuve, id_athlete) {
            // Message de confirmation avant suppression
            if (confirm("Êtes-vous sûr de vouloir supprimer ce résultat?")) {
                // CORRECTION APPLIQUÉE : Assurez-vous que cela pointe vers delete-result.php
                window.location.href = 'delete-result.php?id_epreuve=' + id_epreuve + '&id_athlete=' + id_athlete;
            }
        }
    </script>
</body>

</html>