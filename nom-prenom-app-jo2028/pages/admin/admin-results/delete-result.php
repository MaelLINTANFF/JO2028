<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si les deux IDs sont fournis
if (!isset($_GET['id_epreuve']) || !isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "Paramètres de résultat manquants pour la suppression.";
    header("Location: manage-results.php");
    exit();
}

$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);
$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si les IDs sont valides
if ($id_epreuve === false || $id_athlete === false) {
    $_SESSION['error'] = "IDs de résultat invalides pour la suppression.";
    header("Location: manage-results.php");
    exit();
}

try {
    // Préparez la requête SQL pour supprimer l'enregistrement dans PARTICIPER
    $sql = "DELETE FROM PARTICIPER WHERE id_epreuve = :id_epreuve AND id_athlete = :id_athlete";
    
    // Exécutez la requête SQL avec les paramètres
    $statement = $connexion->prepare($sql);
    $statement->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
    $statement->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
    $statement->execute();

    // Vérifier si une ligne a été affectée
    if ($statement->rowCount() > 0) {
        $_SESSION['success'] = "Le résultat a été supprimé avec succès.";
    } else {
        // Cela peut arriver si le résultat a déjà été supprimé ou si les IDs sont incorrects
        $_SESSION['error'] = "Aucun résultat trouvé avec ces identifiants pour la suppression.";
    }

    // Redirigez TOUJOURS vers la page de gestion des résultats
    header('Location: manage-results.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression du résultat : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    header('Location: manage-results.php');
    exit();
}
?>