<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'athlète est fourni dans l'URL
if (!isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "ID de l'athlète manquant.";
    header("Location: manage-athletes.php");
    exit();
} 

// Récupération et validation de l'ID de l'athlète
$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si l'ID est un entier valide
if ($id_athlete === false) {
    $_SESSION['error'] = "ID de l'athlète invalide.";
    header("Location: manage-athletes.php");
    exit();
}

// Vérification du token CSRF (bonne pratique, même si ici la suppression est via GET pour la simplicité)
if (empty($_SESSION['csrf_token'])) {
    // Si le token n'existe pas, on le crée, mais on ne peut pas le vérifier dans l'URL GET facilement.
    // L'appel doit être sécurisé via POST idéalement, mais on continue ici comme c'est demandé.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
}


try {
    // Préparez la requête SQL pour supprimer l'athlète
    $sql = "DELETE FROM ATHLETE WHERE id_athlete = :param_id_athlete";
    
    // Exécutez la requête SQL avec le paramètre
    $statement = $connexion->prepare($sql);
    $statement->bindParam(':param_id_athlete', $id_athlete, PDO::PARAM_INT);
    $statement->execute();

    // Message de succès
    $_SESSION['success'] = "L'athlète a été supprimé(e) avec succès.";

    // Redirigez vers la page précédente après la suppression
    header('Location: manage-athletes.php');
    exit();
} catch (PDOException $e) {
    // Gestion de l'erreur si la suppression est bloquée par les résultats associés
    if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
         $_SESSION['error'] = "Impossible de supprimer cet athlète car des résultats lui sont encore associés.";
    } else {
         $_SESSION['error'] = "Erreur lors de la suppression de l'athlète : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
    header('Location: manage-athletes.php');
    exit();
}
?>