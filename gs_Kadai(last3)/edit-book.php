<?php
require_once 'db.php';
session_start();

//Check the request came in as POST
if (isset($_POST['update_book'])) {

    if(!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        header('Location: itemDetail.php');
        exit();
    } else {

    $id         = (int)$_POST['id'];
    $title      = $_POST['title'];
    $author     = $_POST['author'];
    $owner      = $_POST['owner'];
    // $isbn      = $_POST['isbn'];
    $location   = $_POST['location'];

    try {

        $stmt = $pdo->prepare("
            UPDATE booklist SET title = ?, author=? , owner = ?, location = ? WHERE id = ?
        ");

        // $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        // $stmt->bindValue(':author', $author, PDO::PARAM_STR);
        // $stmt->bindValue(':owner', $owner, PDO::PARAM_STR);
        // $stmt->bindValue(':isbn', $isbn, PDO::PARAM_STR);
        // $stmt->bindValue(':location', $location, PDO::PARAM_STR);

        $stmt->execute([$title, $author, $owner, $location, $id]);    
    } catch (PDOException $e) {
        die("Update failed: " . $e->getMessage());
    }

    header("Location: itemDetail.php?id=" . $id);
    exit();
    }
}

?>