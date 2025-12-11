<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

$login = $_SESSION['login'];
$nom_utilisateur = $_SESSION['prenom_admin'];
$prenom_utilisateur = $_SESSION['nom_admin'];

// Fonction pour vérifier le token CSRF
function checkCSRFToken()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Token CSRF invalide.');
        }
    }
}

// Générer un token CSRF si ce n'est pas déjà fait
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF
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
    <title>Gestion des Lieux - Jeux Olympiques - Los Angeles 2028</title>
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
                <li><a href="../admin-genres/manage-genres.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li> 
            </ul>
        </nav>
    </header>

    <main>
        <h1>Liste des Lieux</h1>
        <div class="action-buttons">
            <button onclick="openAddPlaceForm()">Ajouter un Lieu</button>
        </div>

        <?php
        require_once("../../../database/database.php");

        try {
            // Requête pour récupérer la liste des lieux depuis la base de données
            $query = "SELECT * FROM LIEU ORDER BY nom_lieu";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table>
                        <tr>
                            <th>Lieu</th>
                            <th>Adresse</th>
                            <th>Code Postal</th>
                            <th>Ville</th>
                            <th>Modifier</th>
                            <th>Supprimer</th>
                        </tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    // Les colonnes de la table LIEU sont : id_lieu, nom_lieu, adresse_lieu, cp_lieu, ville_lieu
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['adresse_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['cp_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['ville_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    // Utilisation de l'id_lieu pour les actions
                    echo "<td><button onclick='openModifyPlaceForm({$row['id_lieu']})'>Modifier</button></td>";
                    echo "<td><button onclick='deletePlaceConfirmation({$row['id_lieu']})'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun lieu trouvé.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
        function openAddPlaceForm() {
            // La page d'ajout doit être nommée 'add-place.php'
            window.location.href = 'add-place.php';
        }

        function openModifyPlaceForm(id_lieu) {
            // La page de modification doit être nommée 'modify-place.php' et passer l'id du lieu
            window.location.href = 'modify-place.php?id_lieu=' + id_lieu;
        }

        function deletePlaceConfirmation(id_lieu) {
            // La page de suppression doit être nommée 'delete-place.php' et passer l'id du lieu
            if (confirm("Êtes-vous sûr de vouloir supprimer ce lieu ? Toutes les épreuves associées pourraient être affectées.")) {
                window.location.href = 'delete-place.php?id_lieu=' + id_lieu;
            }
        }
    </script>
</body>

</html>