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

// Vérifiez si l'ID du pays est fourni dans l'URL
if (!isset($_GET['id_pays'])) {
    $_SESSION['error'] = "ID du pays manquant.";
    header("Location: manage-countries.php");
    exit();
} else {
    // Récupération et validation de l'ID du pays
    $id_pays = filter_input(INPUT_GET, 'id_pays', FILTER_VALIDATE_INT);

    // Vérifiez si l'ID du pays est un entier valide
    if ($id_pays === false) {
        $_SESSION['error'] = "ID du pays invalide.";
        header("Location: manage-countries.php");
        exit();
    } else {
        try {
            // Préparez la requête SQL pour supprimer le pays de la table PAYS
            $sql = "DELETE FROM PAYS WHERE id_pays = :param_id_pays";
            
            // Exécutez la requête SQL avec le paramètre
            $statement = $connexion->prepare($sql);
            $statement->bindParam(':param_id_pays', $id_pays, PDO::PARAM_INT);
            $statement->execute();

            // Message de succès
            $_SESSION['success'] = "Le pays a été supprimé avec succès.";

            // Redirigez vers la page précédente après la suppression
            header('Location: manage-countries.php');
            exit();
        } catch (PDOException $e) {
            // Gestion de l'erreur, notamment si la suppression est bloquée par des clés étrangères
            $_SESSION['error'] = "Erreur lors de la suppression du pays. Au moins un athlète lui est associé : ";
            header('Location: manage-countries.php');
            exit();
        }
    }
}

// Afficher les erreurs en PHP (pour le débogage)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>