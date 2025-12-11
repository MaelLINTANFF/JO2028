<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

$login = $_SESSION['login'];
$nom_utilisateur = $_SESSION['prenom_admin'];
$prenom_utilisateur = $_SESSION['nom_admin'];

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
    <title>Gestion du Calendrier (Épreuves) - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Gestion du Calendrier des Épreuves</h1>
        <div class="action-buttons">
            <button onclick="openAddEventForm()">Ajouter une Épreuve</button>
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
            // Requête pour récupérer les épreuves avec le nom du sport et du lieu associés
            $query = "
                SELECT 
                    E.id_epreuve, 
                    E.nom_epreuve, 
                    E.date_epreuve, 
                    E.heure_epreuve,
                    S.nom_sport, 
                    L.nom_lieu
                FROM 
                    EPREUVE E
                INNER JOIN 
                    SPORT S ON E.id_sport = S.id_sport
                INNER JOIN 
                    LIEU L ON E.id_lieu = L.id_lieu
                ORDER BY 
                    E.date_epreuve, E.heure_epreuve;
            ";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table>
                        <tr>
                            <th>Épreuve</th>
                            <th>Sport</th>
                            <th>Lieu</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Modifier</th>
                            <th>Supprimer</th>
                        </tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $id_epreuve = $row['id_epreuve']; 
                    
                    // Formatage de la date pour un meilleur affichage
                    $date_fr = date("d/m/Y", strtotime($row['date_epreuve'])); 

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_sport'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . $date_fr . "</td>";
                    echo "<td>" . htmlspecialchars($row['heure_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td><button onclick='openModifyEventForm($id_epreuve)'>Modifier</button></td>";
                    echo "<td><button onclick='deleteEventConfirmation($id_epreuve)'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucune épreuve trouvée dans le calendrier.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : Impossible de charger la liste des épreuves. " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
        function openAddEventForm() {
            // Pointez vers la page d'ajout d'une épreuve
            window.location.href = 'add-event.php';
        }

        function openModifyEventForm(id_epreuve) {
            // Pointez vers la page de modification en passant l'ID de l'épreuve
            window.location.href = 'modify-event.php?id_epreuve=' + id_epreuve;
        }

        function deleteEventConfirmation(id_epreuve) {
            // Message de confirmation avant suppression
            if (confirm("Êtes-vous sûr de vouloir supprimer cette épreuve? Cela supprimera également tous les résultats associés!")) {
                // Pointez vers la page de suppression
                window.location.href = 'delete-event.php?id_epreuve=' + id_epreuve;
            }
        }
    </script>
</body>

</html>