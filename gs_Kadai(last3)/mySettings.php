<?php
session_start();
// require_once("auth.php");
require_once("db.php");

$isLoggedIn = isset($_SESSION['user_id']);

?>

<!-- Profile, Edit username
Change password
Log out
App preferences
About -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">    
</head>
<body>
    <header>
        <h2>Settings</h2>
        <a href >Close</a>
    </header>

    <div class="app-container">

        <h2 id="account">Account</h2>

        <?php if ($isLoggedIn): ?>
            <div class="account-actions">
                <p>You're logged in as: <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong></p>
                <form action="logout.php" method="POST">
                    <input type="submit" value="Logout">
                </form>
            </div>

        <?php else: ?>
            <div class="loginform">
            <h3>Log In:</h3>
                <?php if (isset($_GET['error'])): ?>
                    <?php if ($_GET['error'] === 'loginrequired'): ?>
                        <p class="error-msg" style="color: red;">Please log in to add a book.</p>
                    <?php elseif ($_GET['error'] === 'exists'): ?>
                        <p class="error-msg" style="color: red;">That User ID is already taken.</p>
                    <?php elseif ($_GET['error'] === 'empty'): ?>
                        <p class="error-msg" style="color: red;">Please fill in both fields.</p>
                    <?php else: ?>
                        <p class="error-msg" style="color: red;">Invalid User ID or Password</p>
                    <?php endif; ?>
                <?php endif; ?>

                <form id="loginform" action="login_act.php" method="POST">
                    <div class="form-group">
                        <label for="login_id">User ID: </label>
                        <input type="text" name="login_id">
                    </div>
                    <div class="form-group">
                            <label for="login_pw">Password:</label>
                            <input type="text" name="login_pw">
                    </div>
                    <button type="submit" name="login" class="loginBtn">Log in</button>
                    <button type="submit" name="register" class="registerBtn">Register</button>
                </form>
            </div>

            <?php endif; ?>
</div> <!-- end.app-container-->
</body>

<script src="script.js"></script>
</html>