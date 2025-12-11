<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID est fourni
if (!isset($_GET['id_admin'])) {
    $_SESSION['error'] = "ID d'administrateur manquant pour la suppression.";
    header("Location: manage-users.php");
    exit();
}

$id_admin_to_delete = filter_input(INPUT_GET, 'id_admin', FILTER_VALIDATE_INT);
$current_user_id = $_SESSION['id_admin'] ?? null; // Récupérez l'ID de l'utilisateur connecté

// Vérifiez si l'ID est valide
if ($id_admin_to_delete === false) {
    $_SESSION['error'] = "ID d'administrateur invalide pour la suppression.";
    header("Location: manage-users.php");
    exit();
}

// VÉRIFICATION CRITIQUE DE SÉCURITÉ
// Empêche un administrateur de se supprimer lui-même
if ($id_admin_to_delete == $current_user_id) {
    $_SESSION['error'] = "Opération non autorisée : Vous ne pouvez pas supprimer votre propre compte.";
    header("Location: manage-users.php");
    exit();
}


try {
    // Vérifier si au moins un autre administrateur existe
    $queryCount = "SELECT COUNT(*) AS count FROM ADMINISTRATEUR WHERE id_admin != :id_admin_to_delete";
    $statementCount = $connexion->prepare($queryCount);
    $statementCount->bindParam(':id_admin_to_delete', $id_admin_to_delete, PDO::PARAM_INT);
    $statementCount->execute();
    $resultCount = $statementCount->fetch(PDO::FETCH_ASSOC);

    if ($resultCount['count'] < 1) {
        $_SESSION['error'] = "Impossible de supprimer cet administrateur car il est le dernier restant. Veuillez créer un nouvel administrateur avant de supprimer celui-ci.";
        header("Location: manage-users.php");
        exit();
    }
    
    // Préparez la requête SQL pour supprimer l'enregistrement
    $sql = "DELETE FROM ADMINISTRATEUR WHERE id_admin = :id_admin";
    
    // Exécutez la requête SQL avec le paramètre
    $statement = $connexion->prepare($sql);
    $statement->bindParam(':id_admin', $id_admin_to_delete, PDO::PARAM_INT);
    $statement->execute();

    // Vérifier si une ligne a été affectée
    if ($statement->rowCount() > 0) {
        $_SESSION['success'] = "L'administrateur a été supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Aucun administrateur trouvé avec cet identifiant pour la suppression.";
    }

    // Redirigez vers la page précédente après la suppression
    header('Location: manage-users.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression de l'administrateur : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    header('Location: manage-users.php');
    exit();
}
?>