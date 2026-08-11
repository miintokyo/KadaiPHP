<?php
// 1. Bring in $pdo from db.php
session_start();
require_once 'db.php';

$currentUser = $_SESSION['user'] ?? 'me';

if(!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])){
    header('Location: display.php');
    exit;
}

$bookId = (int)$_GET['id'];

// 3. Prepare and execute the SQL query using PDO
try{

// Write SQL with a positional placeholder (?)
    $stmt = $pdo->prepare("SELECT id, title, author, owner, location, cover_image FROM booklist WHERE id = ?");
    
    // Pass parameters as an array in execute()
    $stmt->execute([$bookId]);
    
    // Fetch the single matching record
    $book = $stmt->fetch();

} catch (PDOException $e) {
    die("Database query error: " . $e->getMessage());
}

// 4. Redirect if no matching book is found in the database
if (!$book) {
    header('Location: display.php');
    exit;
}

$isOwner = ($book['owner'] === $currentUser || $book['owner'] === 'me');

$fromSection = isset($_GET['from']) && $_GET['from'] === 'me' ? 'me' : 'tana';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['title']) ?> - Details</title>
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
</head>
<body>

    <main class="detail-container">
        <header class="detail-header">
            <a href="display.php#<?= $fromSection ?>" class="close-btn">&times;</a>
            <?php if ($isOwner): ?>
                <button type="button" class="edit-btn" id="edititem-btn">Edit</button>
            <?php endif; ?>
        </header>
        <section class="detail-media">
            <img src="<?= htmlspecialchars($book['cover_image']??'placeholder.jpg') ?>"
            alt="Cover for <?=htmlspecialchars($book['title']) ?>">
        </section>

        <!--VIEW MODE CONTAINER -->
        <section id="view-mode" class="detail-info">
            <h1 class="book-title"><?= htmlspecialchars($book['title']) ?></h1>
            <p class="book-author"><strong>Author: </strong><?= htmlspecialchars($book['author']) ?></p>
            <p class="book-owner"><strong>Owner: </strong><?= htmlspecialchars($book['owner']) ?></p>
            <?php if (!empty($book['location'])): ?>
                <p class="book-location"><strong>Location: </strong><?= htmlspecialchars($book['location']) ?></p>
            <?php endif; ?>
        </section>

        <!--EDIT MODE CONTAINER -->
        <?php if ($isOwner): ?>
        <section id="edit-mode" class="detail-info hidden">
            <form id="editBookForm" action="edit-book.php" method="POST">
                <input type="hidden" name="id" value="<?= $book['id'] ?>">

                <div class="form-group">
                    <label for="edit-title">Title</label>
                    <input type="text" id="edit-title" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="edit-author">Author</label>
                    <input type="text" id="edit-author" name="author" value="<?= htmlspecialchars($book['author']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="edit-owner">Owner</label>
                    <input type="text" id="edit-owner" name="owner" value="<?= htmlspecialchars($book['owner']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="edit-location">Location</label>
                    <input type="text" id="edit-location" name="location" value="<?= htmlspecialchars($book['location']) ?>" required>
                </div>
                
                <div class="form-actions" style="margin-top: 15px; display: flex; gap: 10px;">
                    <button type="button" id="cancel-edit-btn" class="btn-secondary">Cancel</button> 
                    <button type="submit" name="update_book" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </section>
        <?php endif; ?>
    </main>
    <script src="script.js"></script>
</body>
</html>