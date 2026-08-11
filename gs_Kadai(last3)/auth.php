<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(){
    if(!isset($_SESSION['user_id'])) {
        header('Location: mySettings.php?error=unauthorized');
        exit();
    }
}

function require_admin(){
    require_login();
    if(empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header('Location: display.php'error=forbidden);
        exit();
    }
}
?>