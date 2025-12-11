<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID est fourni
if (!isset($_GET['id_genre'])) {
    $_SESSION['error'] = "ID de genre manquant pour la suppression.";
    header("Location: manage-genres.php");
    exit();
}

$id_genre = filter_input(INPUT_GET, 'id_genre', FILTER_VALIDATE_INT);

// Vérifiez si l'ID est valide
if ($id_genre === false) {
    $_SESSION['error'] = "ID de genre invalide pour la suppression.";
    header("Location: manage-genres.php");
    exit();
}

try {
    // Préparez la requête SQL pour supprimer l'enregistrement
    $sql = "DELETE FROM GENRE WHERE id_genre = :id_genre";
    
    // Exécutez la requête SQL avec le paramètre
    $statement = $connexion->prepare($sql);
    $statement->bindParam(':id_genre', $id_genre, PDO::PARAM_INT);
    $statement->execute();

    // Vérifier si une ligne a été affectée
    if ($statement->rowCount() > 0) {
        $_SESSION['success'] = "Le genre a été supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Aucun genre trouvé avec cet identifiant pour la suppression.";
    }

    // Redirigez vers la page précédente après la suppression
    header('Location: manage-genres.php');
    exit();

} catch (PDOException $e) {
    // Code 23000 = Contrainte d'intégrité (clé étrangère)
    if ($e->getCode() == '23000') {
        $_SESSION['error'] = "Impossible de supprimer ce genre car il est lié à un ou plusieurs athlètes.";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression du genre : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
    header('Location: manage-genres.php');
    exit();
}
?>