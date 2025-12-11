<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/styles-computer.css">
    <link rel="stylesheet" href="../css/styles-responsive.css">
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <title>Calendrier des Épreuves - Jeux Olympiques - Los Angeles 2028</title>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="sports.php">Sports</a></li>
                <li><a href="events.php">Calendrier des évènements</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="login.php">Accès administrateur</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Calendrier des Épreuves</h1>

        <?php
        require_once("../database/database.php");

        try {

            $query = "SELECT 
                        e.nom_epreuve,
                        s.nom_sport,
                        e.date_epreuve,
                        e.heure_epreuve,
                        l.nom_lieu,
                        l.adresse_lieu,
                        l.cp_lieu,
                        l.ville_lieu
                      FROM EPREUVE e
                      JOIN SPORT s ON e.id_sport = s.id_sport
                      JOIN LIEU l ON e.id_lieu = l.id_lieu
                      ORDER BY e.date_epreuve, e.heure_epreuve";

            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table>";
                echo "<tr>
                        <th>Épreuve</th>
                        <th>Sport</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Lieu</th>
                        <th>Adresse</th>
                      </tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    // Formatage de la date
                    $dateFormatee = date('d/m/Y', strtotime($row['date_epreuve']));

                    // Formatage de l'heure
                    $heureFormatee = date('H:i', strtotime($row['heure_epreuve']));

                    // Adresse complète
                    $adresseComplete = $row['adresse_lieu'] . ', ' . $row['cp_lieu'] . ' ' . $row['ville_lieu'];

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_sport'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($dateFormatee, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($heureFormatee, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($adresseComplete, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucune épreuve trouvée.</p>";
            }
        } catch (PDOException $e) {
            // Gestion d'erreur améliorée
            echo "<p style='color: red;'>Erreur : Impossible de récupérer le calendrier des épreuves. Veuillez réessayer plus tard.</p>";
            error_log("Erreur PDO : " . $e->getMessage());
        }

        // Définir le niveau d'affichage des erreurs (utile en phase de développement)
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        ?>

        <p class="paragraph-link">
            <a class="link-home" href="../index.php">Retour Accueil</a>
        </p>

    </main>
    <footer>
        <figure>
            <img src="../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>

</html>