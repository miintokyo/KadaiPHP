<?php
session_start();
require_once 'db.php';

$login_id   = trim($_POST['login_id'] ?? '');
$login_pw   = $_POST['login_pw'] ?? '';
$name       = trim($_POST['name'] ?? '');

if (empty($login_id) || empty($login_pw)) {
    header('Location: mySettings.php?error=empty');
    exit();
}

//ACTION 1. REGISTER NEW USER
if (isset($_POST['register'])) {
    //Fallback to login_id if display name was left blank
    if (empty($name)) {
        $name = $login_id;
    }
    //Check if login_id already exists in database
    $stmt = $pdo->prepare('SELECT id FROM user WHERE login_id = :login_id');
    $stmt->execute([':login_id' => $login_id]);

    if($stmt->fetch()) {
        //UserID taken
        header('Location: mySettings.php?error=exists');
        exit();
    }

    $hashed_pw = password_hash($login_pw, PASSWORD_DEFAULT);

    $sql = 'INSERT INTO user (login_id, login_pw, name, is_admin) VALUES (:login_id, :login_pw, :name, 0)';
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        ':login_id' => $login_id,
        ':login_pw' => $hashed_pw,
        ':name' => $name
    ]);

    if($success) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $name;
        $_SESSION['is_admin']=0;

        header('Location: display.php');
        exit();
    } else {
        header('Location: mySettings.php?error=failed');
        exit();
    }
}

if (isset($_POST['login'])) {

    $stmt = $pdo->prepare('SELECT * FROM user WHERE login_id= :login_id');
    $stmt->execute([':login_id' => $login_id]);
    $user = $stmt->fetch();

    if($user){
        //Check 1: verify against password hash()
        $isHashedMatch = password_verify($login_pw, $user['login_pw']);
        //Check 2: verify against plain text (for old dummy data)
        $isPlainMatch = ($login_pw === $user['login_pw']);

        if($isHashedMatch || $isPlainMatch) {
            session_regenerate_id(true);
            //Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin'] = $user['is_admin'];

        header('Location: display.php');
        exit();
    } else {
    header('Location: mySettings.php?error=1');
    exit();
    }
    }
}
?>
