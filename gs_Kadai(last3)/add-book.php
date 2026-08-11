<?php
require_once 'db.php';//$pdo = new PDO("mysql: host=localhost; xxxx)
session_start();

// Block guests from adding books
if (!isset($_SESSION['user_id'])) {
    header('Location: mySettings.php?error=loginrequired');
    exit();
}

$title      = $_POST['title'];
$author     = $_POST['author'];
$isbn       = $_POST['isbn'];
// Fallback for cover_image so it is never NULL
//$cover_image = !empty($_POST['cover_image']) ? $_POST['cover_image'] : ''; 
// Or use a default placeholder image:
$cover_image = !empty($_POST['cover_image']) ? $_POST['cover_image'] : 'https://via.placeholder.com/150';

$category   = $_POST['category'] ?? null;

$owner = $_SESSION['user_name'] ?? 'guest';

// Prepare the query first, then execute it by passing the actual values in an array
$stmt = $pdo->prepare("
    INSERT INTO booklist (title, author, cover_image, category, createdat, isbn, owner) VALUES(:title, :author, :cover_image, :category, NOW(), :isbn, :owner);
");

$stmt->bindValue(':title', $title, PDO::PARAM_STR);
$stmt->bindValue(':author', $author, PDO::PARAM_STR);
$stmt->bindValue(':cover_image', $cover_image, PDO::PARAM_STR);
$stmt->bindValue(':category', $category ? PDO::PARAM_STR : PDO::PARAM_NULL);
$stmt->bindValue(':isbn', $isbn, PDO::PARAM_STR);
$stmt->bindValue(':owner', $owner, PDO::PARAM_STR);

$stmt->execute();

header("Location: display.php#me");
exit();
?>