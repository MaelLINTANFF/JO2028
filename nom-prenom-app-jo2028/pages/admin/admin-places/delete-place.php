<?php
session_start();
require_once("../../../database/database.php");

// Note : La vérification CSRF via POST n'est généralement pas utilisée ici 
// car la suppression se fait via un lien GET (dû au `window.location.href` du JS).
// Si vous passez à une méthode POST/formulaire plus tard, ce bloc sera utile.

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Génération du token CSRF si ce n'est pas déjà fait (même si non utilisé pour le GET)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF sécurisé
}

// Vérifiez si l'ID du lieu est fourni dans l'URL
if (!isset($_GET['id_lieu'])) {
    $_SESSION['error'] = "ID du lieu manquant.";
    header("Location: manage-places.php");
    exit();
} else {
    // Récupération et validation de l'ID du lieu
    $id_lieu = filter_input(INPUT_GET, 'id_lieu', FILTER_VALIDATE_INT);

    // Vérifiez si l'ID du lieu est un entier valide
    if ($id_lieu === false) {
        $_SESSION['error'] = "ID du lieu invalide.";
        header("Location: manage-places.php");
        exit();
    } else {
        try {
            // Préparez la requête SQL pour supprimer le lieu de la table LIEU
            $sql = "DELETE FROM LIEU WHERE id_lieu = :param_id_lieu";
            
            // Exécutez la requête SQL avec le paramètre
            $statement = $connexion->prepare($sql);
            $statement->bindParam(':param_id_lieu', $id_lieu, PDO::PARAM_INT);
            $statement->execute();

            // Message de succès
            $_SESSION['success'] = "Le lieu a été supprimé avec succès.";

            // Redirigez vers la page précédente après la suppression
            header('Location: manage-places.php');
            exit();
        } catch (PDOException $e) {
            // Gestion de l'erreur, notamment si la suppression est bloquée par des clés étrangères
            $_SESSION['error'] = "Erreur lors de la suppression du lieu. Vérifiez qu'aucune épreuve ne lui est associée : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            header('Location: manage-places.php');
            exit();
        }
    }
}

// Afficher les erreurs en PHP (pour le débogage)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>