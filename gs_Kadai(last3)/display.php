<?php
session_start();
require_once 'db.php';

$currentUser = $_SESSION['username'] ?? 'me';

function fetchBooks($pdo, $sql){
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

//2. Fetch my books
// Now executing queries takes just one line each!
$sql_mine = "SELECT id, title, author, owner, location, cover_image FROM booklist WHERE owner = 'me' ORDER BY id";
$result_mine = fetchBooks($pdo, $sql_mine);

// 2. Helper to render the HTML cards cleanly using array iteration
function renderBookCards($result, $currentUser, $showEdit = false, $fromSection = 'tana') {
    if (!empty($result)) {
        // loops over the whole array ($result), and on each pass gives you one book, stored in $book
        foreach ($result as $book) {
            $isOwner = ($book['owner'] === $currentUser || $book['owner'] === 'me');
             ?>
            <div class="item-card" data-id="<?= htmlspecialchars($book['id']) ?>">
                <div class="image-container">
                    <img src="<?= htmlspecialchars($book['cover_image'] ?? 'placeholder.jpg') ?>" alt="Cover">
                    <div class="overlay">

                        <div class="overlay-actions">
                            <a href="itemDetail.php?id=<?= urlencode($book['id']) ?>&from=<?= urlencode($fromSection) ?>" class="btn-overlay view-btn">View</a>

                            <!-- only show edit if explicitly enabled and user is owner-->
                            <?php if ($showEdit && $isOwner): ?>
                                <a href="itemDetail.php?id=<?= urlencode($book['id']) ?>&mode=edit&from=<?= urlencode($fromSection) ?>" class="btn-overlay edit-btn">Edit</a>
                            <?php endif; ?>
                        </div>

                        <div class="itemDetails">
                            <h3 class="title"><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="author">By: <?= htmlspecialchars($book['author']) ?></p>
                        </div>
                    </div>
                </div>
                <p class="owner">Owner: <?= htmlspecialchars($book['owner']) ?></p>
            </div>
        <?php }
    } else {
        echo "<p>No books in this category!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display.php</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= isset($_SESSION['user_id']) ? 'user-logged-in' : 'user-logged-out'; ?>">
    <div class="app-container">

    <?php 
    $pageTitle = "App";
    $settingsLink = "app";
    include 'header.php'; ?>

    <div class="main">

        <div id="bookModalOverlay" class="modal-overlay hidden">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalTitle">Add  a New Book</h3>
                    <button type="button" id="cancelBtn" class="close-btn">&times;</button>
                </div>

                <!-- Book Entry Form-->
                <form id="addBookForm" action="add-book.php" method="POST">
                    <div class="form-group">
                        <label for='isbn'>ISBN (Scanned or Manual)</label>
                        <div class="input-with-button">
                            <input type="hidden" id="cover_image" name="cover_image">
                            <input type="hidden" id="category" name="category">
                            <input type="text" id="isbn" name="isbn" placeholder="e.g., 9780140449136">
                            <button type="button" id="scanIsbnBtn" class="btn-secondary">Populate form</button>
                        </div>
                    </div>
                
                    <div class="form-group">
                        <label for ="title">Title* </label>
                        <input type="text" id="title" name="title" required placeholder="Book title">
                    </div>

                    <div class="form-group">
                        <label for="author">Author *</label>
                        <input type="text" id="author" name="author" required placeholder="Author name">
                    </div>

                    <div class="modal-actions">
                        <button type="button" id="modalCancelBtn" class="btn-secondary">Cancel</button>
                        <button type="submit" name="submit_book" class="btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
        
        <h1>Me</h1>
        <div class="category">
            <h2>Current Stock</h2>
            <button id="addBtn">Add +</button>
        </div>

        <div id="interactive-search" class="viewport hidden" style="width: 100%; max-width: 400px;"></div>

        <div class="item-cards">
            <?php renderBookCards($result_mine, $currentUser, true, 'me'); ?>
        </div>

    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.19.1/umd/index.min.js"></script>
<script src="script.js"></script>
</html>