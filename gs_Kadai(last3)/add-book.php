<?php
require_once 'db.php';//$pdo = new PDO("mysql: host=localhost; xxxx)

$title      = $_POST['title'];
$author     = $_POST['author'];
$isbn       = $_POST['isbn'];
$coverImage = $_POST['cover_image'] ?? null;
$category   = $_POST['category'] ?? null;

$owner = $_SESSION['user_id'] ?? 'guest';

// Prepare the query first, then execute it by passing the actual values in an array
$stmt = $pdo->prepare("
    INSERT INTO booklist (title, author, cover_image, category, createdat, isbn, owner) VALUES(:title, :author, :cover_image, :category, NOW(), :isbn, :owner);
");

$stmt->bindValue(':title', $title, PDO::PARAM_STR);
$stmt->bindValue(':author', $author, PDO::PARAM_STR);
$stmt->bindValue(':cover_image', $coverImage, $coverImage ? PDO::PARAM_STR : PDO::PARAM_NULL);
$stmt->bindValue(':category', $category ? PDO::PARAM_STR : PDO::PARAM_NULL);
$stmt->bindValue(':isbn', $isbn, PDO::PARAM_STR);
$stmt->bindValue(':owner', $owner, PDO::PARAM_STR);

$stmt->execute();

header("Location: display.php#me");
exit();
?>