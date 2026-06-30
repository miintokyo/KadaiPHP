<?php
//////////////////////////
//Create the STATEMENT!!//
//////////////////////////

// 1. Connect to the database (adjust db_name if yours is different)
try {
    $db_name = "php02book"; // 👈 Change this to your actual DB name
    $db_id   = "root";               // XAMPP default username
    $db_pw   = "";                   // XAMPP default password (empty)
    $db_host = "localhost";
    
    $pdo = new PDO('mysql:dbname=' . $db_name . ';charset=utf8;host=' . $db_host, $db_id, $db_pw);
} catch (PDOException $e) {
    exit('DB Connection Error: ' . $e->getMessage());
}

// 2. Prepare and execute the SQL query to get your books
$sql = "SELECT * FROM booklist"; // 👈 Change this to your actual table name
$stmt = $pdo->prepare($sql);
$status = $stmt->execute();

// 3. Check for errors
if ($status == false) {
    $error = $stmt->errorInfo();
    exit("SQL Error: " . $error[2]);
}

// $stmt is now successfully created and filled with data!
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main">
    
        <div class="title">
            <h1>Book DB</h1>
            <button class="addBook">Add +</button>
        </div>

        <div class="bookCards">
            <?php while($result = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

                <div class="bookCard">
                    <img src="<?=htmlspecialchars($result['imageAPI'], ENT_QUOTES, 'UTF-8')?>" alt="Book Cover">
                    <div class="bookOverlay">

                        <h3 class="bookTitle">
                            <?=htmlspecialchars($result['name'],ENT_QUOTES,'UTF-8')?>
                        </h3>
                        <p class="bookAuthor">
                            <?=htmlspecialchars($result['author'],ENT_QUOTES,'UTF-8')?>
                        </p>
                    </div>
                </div>
            <?php endwhile;?>
        </div>
    </div>
</body>
<script>
    const addBtn = document.querySelector(".addBook")
    addBtn.addEventListener("click",function(){
        addBook()
    })

    function addBook(){
        window.location.href="Register.php";        
    }
</script>
</html>