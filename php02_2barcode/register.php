<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>
    <div class="main">
    
        <div class="title">
            <h1>Book DB</h1>
        </div>

        <div id="reader" style="width: 100%; max-width: 400px; margin: 10px auto; border-radius: 8px; overflow: hidden;"></div>
        <button type="button" id="startScanBtn" style="margin-bottom: 15px;">📷 Open Barcode Scanner</button>

        <form class="form" method="POST" action="process.php">
            <label for ="name">Name: </label>
            <input type="text" name="name" id="name" class="inputName">

            <label for ="author">Author: </label>
            <input type="text" name="author" id="author" class="inputAuthor" placeholder="First name, Last name">

            <label for ="status">Reading status: </label>
            <select name="status" id="status" class="inputStatus">
                <option value="toRead">To-Read</option>
                <option value="reading">Reading</option>
                <option value="completed">Completed</option>
            </select>

            <label for ="comment">Comment: </label>
            <textarea name="comment" id="comment" rows="4" placeholder=""></textarea>

            <button type="button" id="search" class="searchBtn">Search</button>

            <div class=apiResult>
                <img class="coverPreview" src="" alt="" style="max-width: 100px; display: none;">
                <p class="nameAPI"></p>
                <p class="authorAPI"></p>
                <p class="reviewPreview" style="font-size: 0.9rem; color: #666; font-style: italic; margin-top: 5px;"></p>
            </div>

            <input type="hidden" name="imageAPI" id="imageAPI">
            <input type="hidden" name="reviewAPI" id="reviewAPI">
            <button id=submit>Save</button>

        </form>
    </div>
</body>
<script>
//Javascript for fetching API data
 // 1. Point fetch to the URL you want to request data from

const searchBtn = document.querySelector(".searchBtn")

searchBtn.addEventListener("click", function(event){

    //1. Stop the form from submitting/refreshing page immediately
    event.preventDefault();
    // 2. Grab what the user typed into the Name input field
    const userQuery = document.querySelector(".inputName").value;
    
    if(!userQuery) {
        alert("Please type a book name first!");
        return;
    }

    // ⏳ Disable the button immediately to stop rapid clicking
    searchBtn.disabled = true;
    searchBtn.textContent = "Searching...";

    // 3. Inject the user's typed value directly into the API URL string
    const apiKey = "";
    fetch(`https://www.googleapis.com/books/v1/volumes?q=${encodeURIComponent(userQuery)}&key=${apiKey}`)
        .then(function(response) {
            // 🔁 2. Handle 429 Gracefully right here
            if (response.status === 429) {
                throw new Error("Rate limit exceeded (429). Please wait a minute before trying again.");
            }
            return response.json(); 
        })
        .then(function(data) {
            console.log(data); // Look closely at this object in your console!
            // 3. Re-enable button when successful
            searchBtn.disabled = false;
            searchBtn.textContent = "Search";

            // SAFETY CHECK: If Google returns an error or 0 results, stop here!
            if (!data.items || data.items.length === 0) {
                alert("No books found or API limit reached. Please type the details manually!");
                return; 
            }

            // Extracting data safely from the first match found by Google
            const bookInfo = data.items[0].volumeInfo;
            
            // 4. Fill in your hidden inputs so process.php can see them!
            document.querySelector("#imageAPI").value = bookInfo.imageLinks ? bookInfo.imageLinks.thumbnail : "";

            const fullDescription = bookInfo.description ? bookInfo.description : "No review available.";
            document.querySelector("#reviewAPI").value = fullDescription;

            // 5. Update the UI text so the user sees a confirmation preview
            document.querySelector(".nameAPI").textContent = "Found: " + bookInfo.title;
            document.querySelector(".authorAPI").textContent = "By: " + (bookInfo.authors ? bookInfo.authors.join(", ") : "Unknown");

            // New: Slice the description down to a neat little snippet (e.g., 120 characters) + "..."
            const reviewSnippet = fullDescription.length > 120 ? fullDescription.substring(0, 120) + "..." : fullDescription;
            document.querySelector(".reviewPreview").textContent = "Description: " + reviewSnippet;

            // (Optional) Auto-fill the author input field if it's empty
            const authorInput = document.querySelector(".inputAuthor");
            if(!authorInput.value && bookInfo.authors) {
                authorInput.value = bookInfo.authors[0];
            }
            
            // Show the book cover preview visually
            if(bookInfo.imageLinks) {
                const previewImg = document.querySelector(".coverPreview");
                previewImg.src = bookInfo.imageLinks.thumbnail;
                previewImg.style.display = "block";
            }
        })
        .catch(function(error) {
            console.error("Something went wrong:", error);
            // ⏳ Always re-enable the button if an error occurs so they can try again!
            searchBtn.disabled = false;
            searchBtn.textContent = "Search";            
        });
});


// Initialize the HTML5 QR/Barcode Code Scanner instance
const html5QrcodeScanner = new Html5Qrcode("reader");
const startScanBtn = document.getElementById("startScanBtn");

startScanBtn.addEventListener("click", function() {
    startScanBtn.disabled = true;
    startScanBtn.textContent = "Camera Initializing...";

    // Start the camera stream
    // 'environment' requests the back/rear-facing camera on smartphones
    html5QrcodeScanner.start(
        { facingMode: "environment" }, 
        {
            fps: 10,    // Scans 10 frames per second
            qrbox: { width: 300, height: 150 } // Box shape optimized for long book barcodes
        },
        onScanSuccess,
        onScanFailure
    ).catch(err => {
        console.error("Camera access failed:", err);
        alert("Could not access the rear camera.");
        startScanBtn.disabled = false;
        startScanBtn.textContent = "📷 Open Barcode Scanner";
    });
});

// This fires immediately when a barcode is successfully detected!
function onScanSuccess(decodedText, decodedResult) {
    console.log(`Barcode Detected: ${decodedText}`);
    
    // Stop the camera feed to save battery and processing power
    html5QrcodeScanner.stop().then(() => {
        startScanBtn.disabled = false;
        startScanBtn.textContent = "📷 Open Barcode Scanner";
    });

    // Vibrate the phone slightly for a native app feel (supported on Android/Chrome)
    if (navigator.vibrate) navigator.vibrate(200);

    // Feed the extracted ISBN directly into your Google Books Search function!
    fetchBookDataByISBN(decodedText);
}

function onScanFailure(error) {
    // This fires continuously while hunting for a barcode. 
    // Leave it empty so it fails silently until a clean code matches.
}

// Reusable fetch engine specifically optimized for target ISBN numbers
function fetchBookDataByISBN(isbnCode) {
    const apiKey = "AIzaSyCZTtAjNPLdOUJw-WWf6aV9wXYYYouCIzY";
    const searchBtn = document.querySelector(".searchBtn");
    
    searchBtn.disabled = true;
    searchBtn.textContent = "Searching ISBN...";

    // Using the explicit isbn: parameter forces Google to find the EXACT match
    fetch(`https://www.googleapis.com/books/v1/volumes?q=isbn:${isbnCode}&key=${apiKey}`)
        .then(response => response.json())
        .then(data => {
            searchBtn.disabled = false;
            searchBtn.textContent = "Search";

            if (!data.items || data.items.length === 0) {
                alert(`No data found on Google Books for ISBN: ${isbnCode}. Please enter manually.`);
                return;
            }

            const bookInfo = data.items[0].volumeInfo;

            // Auto-populate the form inputs instantly!
            document.querySelector(".inputName").value = bookInfo.title || "";
            document.querySelector(".inputAuthor").value = bookInfo.authors ? bookInfo.authors.join(", ") : "";
            
            // Stuff your hidden fields for process.php
            document.querySelector("#imageAPI").value = bookInfo.imageLinks ? bookInfo.imageLinks.thumbnail : "";
            document.querySelector("#reviewAPI").value = bookInfo.description || "No review available.";

            // Show visual confirmation on the screen
            document.querySelector(".nameAPI").textContent = "Found: " + bookInfo.title;
            document.querySelector(".authorAPI").textContent = "By: " + (bookInfo.authors ? bookInfo.authors.join(", ") : "Unknown");
            
            if (bookInfo.imageLinks) {
                const previewImg = document.querySelector(".coverPreview");
                previewImg.src = bookInfo.imageLinks.thumbnail;
                previewImg.style.display = "block";
            }
        })
        .catch(error => {
            console.error("API error:", error);
            searchBtn.disabled = false;
            searchBtn.textContent = "Search";
        });
}
</script>
</html>