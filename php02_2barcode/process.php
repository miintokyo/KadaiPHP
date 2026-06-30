<?php 
$name       = $_POST['name'];
$author     = $_POST['author'];
$comment    = $_POST['comment'];
$status     = $_POST['status'];
$imageAPI    = $_POST['imageAPI'];
$reviewAPI  = $_POST['reviewAPI'];

// Fallback: If JavaScript didn't find an image, use a placeholder
if (empty($imageAPI)) {
    $imageAPI = "https://via.placeholder.com/150x220?text=No+Cover";
} else {
    // Force https on the google URL so it loads securely
    $imageAPI = str_replace("http://", "https://", $imageAPI);
}
// ==========================================
// 2. CONNECTING TO THE DATABASE (PDO - PHP data objects)
// ==========================================
// We use a try-catch block here. "Try" to run the code, and if something breaks, "Catch" the error so the script doesn't crash catastrophically.

// Creating a new PDO object to establish a connection.
    // Argument 1: Data Source Name (DSN) - contains DB type (mysql), DB name, character set, and host.
    // Argument 2: DB Username ('root')
    // Argument 3: DB Password ('' is empty by default in XAMPP)
try {
    $db_name = "php02book"; // 👈 Your DB name
    $db_id   = "root";
    $db_pw   = "";
    $db_host = "localhost";
    $pdo = new PDO('mysql:dbname=' . $db_name . ';charset=utf8;host=' . $db_host, $db_id, $db_pw);

    // Configure PDO to throw "Exceptions" (errors) if something goes wrong with SQL. Highly recommended for debugging.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
    exit('DB Connection Error: ' . $e->getMessage());
}

// ==========================================
// 3. PREPARING AND EXECUTING THE SQL STATEMENT
// ==========================================

// 3.1 Prepare the SQL template
// We use placeholders (:name, :email, :content),
// instead of direct variables to prevent SQL Injection attacks.

//stmt = "STATEMENT", pdo = "PHP DATA OBJECTS",
//Asking database to pre-compile the SQL query template.
//The databases processes it and hands back at "Statement Object"
//which is then stored inside $stmt 

//Prepare (prepare statement object)
$stmt = $pdo->prepare("
    INSERT INTO booklist(name, author, comment, status, imageAPI, reviewAPI)
    VALUES(:name, :author, :comment, :status, :imageAPI, :reviewAPI)
");

$stmt->bindValue(':name', $name, PDO::PARAM_STR);
$stmt->bindValue(':author', $author, PDO::PARAM_STR);
$stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
$stmt->bindValue(':status', $status, PDO::PARAM_STR);
$stmt->bindValue(':imageAPI', $imageAPI, PDO::PARAM_STR);
$stmt->bindValue(':reviewAPI', $reviewAPI, PDO::PARAM_STR);

//Execute the SQL query
$status = $stmt->execute();

header("Location: index.php");
exit();