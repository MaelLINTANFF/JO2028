<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Génération du token CSRF si ce n'est pas déjà fait (bonne pratique)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
}

// Vérifiez si l'ID de l'épreuve est fourni dans l'URL
if (!isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'épreuve manquant.";
    header("Location: manage-events.php");
    exit();
} else {
    // Récupération et validation de l'ID de l'épreuve
    $id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

    // Vérifiez si l'ID est un entier valide
    if ($id_epreuve === false) {
        $_SESSION['error'] = "ID de l'épreuve invalide.";
        header("Location: manage-events.php");
        exit();
    } else {
        try {
            // Préparez la requête SQL pour supprimer l'épreuve
            $sql = "DELETE FROM EPREUVE WHERE id_epreuve = :param_id_epreuve";
            
            // Exécutez la requête SQL avec le paramètre
            $statement = $connexion->prepare($sql);
            $statement->bindParam(':param_id_epreuve', $id_epreuve, PDO::PARAM_INT);
            $statement->execute();

            // Message de succès
            $_SESSION['success'] = "L'épreuve a été supprimée avec succès.";

            // Redirigez vers la page précédente après la suppression
            header('Location: manage-events.php');
            exit();
        } catch (PDOException $e) {
            // Gestion de l'erreur, notamment si la suppression est bloquée par les résultats associés
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                 $_SESSION['error'] = "Impossible de supprimer cette épreuve car des résultats lui sont encore associés.";
            } else {
                 $_SESSION['error'] = "Erreur lors de la suppression de l'épreuve : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
            header('Location: manage-events.php');
            exit();
        }
    }
}

// Afficher les erreurs en PHP (pour le débogage)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>