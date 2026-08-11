
document.addEventListener("DOMContentLoaded", () => {
    const currentHash = window.location.hash.substring(1); 

    let currentActiveTabId = "tana-page";
    // =====================
    // NAVIGATION
    // =====================
    const navItems = document.querySelectorAll(".bottom-nav .nav-item");
    const headerTitle = document.getElementById("header-title");
    const pages = document.querySelectorAll(".page");
    const settingsButton = document.querySelector(".top-nav-item");

function switchTab(tab){
    if (!tab) return;

    const targetTab = document.querySelector(`.bottom-nav .nav-item[href="#${tab}"]`);
    const pageId = tab + "-page";
    const targetPage = document.getElementById(pageId);

    if(targetPage && targetTab) {
        navItems.forEach(nav => nav.classList.remove("is-active"));
        pages.forEach(page => page.classList.add("hidden"));

        targetTab.classList.add("is-active");
        targetPage.classList.remove("hidden");

        const labelEl = targetTab.querySelector(".nav-label");
        if(labelEl && headerTitle) {
            headerTitle.textContent = labelEl.textContent;
        }

        if (settingsButton) {
            const sectionMap = {
                me: "account", thoughts: "thoughts", tana: "tana", notifications: "notifications", messages: "messages"
            };
            settingsButton.href = `mySettings.php#${sectionMap[tab] || 'account'}`;
        }
    }
}

// Handle initial hash load if present
    if (currentHash) {
        switchTab(currentHash);
    }

    navItems.forEach(item => {
        item.addEventListener("click", (event) => {
            event.preventDefault();
            const tab = item.getAttribute("href").substring(1);
            switchTab(tab);
            // navItems.forEach(nav => nav.classList.remove("is-active"));
            // item.classList.add("is-active");
            // pages.forEach(page => page.classList.add("hidden"));
            // const tab = item.getAttribute("href").substring(1);
            // const pageId = tab + "-page";
            // const targetPage = document.getElementById(pageId);
            // if (targetPage) targetPage.classList.remove("hidden");
            // const labelText = item.querySelector(".nav-label").textContent;
            // const sectionMap = {
            //     me: "account", thoughts: "thoughts", tana: "tana",
            //     notifications: "notifications", messages: "messages"
            // };
            // headerTitle.textContent = labelText;
            // settingsButton.href = `mySettings.php#${sectionMap[tab] || 'account'}`;
        });
    });

// Grab the Populate button element
const scanIsbnBtn = document.getElementById("scanIsbnBtn");

if (scanIsbnBtn) {
    scanIsbnBtn.addEventListener("click", async () => {
        // Read input value and remove any hyphens or spaces
        const isbnInput = document.getElementById("isbn").value.trim().replace(/[- ]/g, "");

        if (!isbnInput) {
            alert("Please enter or scan an ISBN first.");
            return;
        }

        // Optional: Disable button and show loading state
        scanIsbnBtn.disabled = true;
        scanIsbnBtn.textContent = "Loading...";

        await fetchBookDetails(isbnInput);

        // Reset button state
        scanIsbnBtn.disabled = false;
        scanIsbnBtn.textContent = "Populate";
    });
}

    // =====================
    // MODAL + SCANNER
    // =====================
    const addBookBtn = document.getElementById("addBtn");
    const addBookForm = document.getElementById("addBookForm");
    const scannerSection = document.getElementById("scannerSection");
    let codeReader = null;

    if (addBookBtn) {

        addBookBtn.addEventListener("click", () => {
        document.getElementById('bookModalOverlay').classList.remove('hidden');
        scannerSection.classList.remove("hidden");
        addBookForm.classList.add("hidden");

        codeReader = new ZXing.BrowserMultiFormatReader();
        codeReader.decodeFromVideoDevice(null, 'interactive-modal', (result, err) => {
            if (result) {
                const isbn = result.getText();
                console.log("Scanned ISBN:", isbn);
                handleScanSuccess(isbn);
            }
            // errors fire constantly while scanning — safe to ignore
        });
    });
    }


    // =====================
    // STOP SCANNER HELPER
    // =====================
    async function stopScanner() {
        if (codeReader) {
            codeReader.reset();
            codeReader = null;
        }
    }

    // =====================
    // SCAN SUCCESS
    // =====================
    async function handleScanSuccess(isbn) {
        document.getElementById("isbn").value = isbn;
        await stopScanner();
        scannerSection.classList.add("hidden");
        addBookForm.classList.remove("hidden");
        await fetchBookDetails(isbn);
    }

    // =====================
    // SKIP SCAN BUTTON
    // =====================
    const cancelScanButton = document.getElementById("cancelScanButton");
    if (cancelScanButton) {
        cancelScanButton.addEventListener("click", async () => {
            await stopScanner();
            scannerSection.classList.add("hidden");
            addBookForm.classList.remove("hidden");
        });
    }
    // =====================
    // CLOSE MODAL
    // =====================
    const closeModal = async () => {
        await stopScanner();
        document.getElementById("bookModalOverlay").classList.add("hidden");
        scannerSection.classList.add("hidden");
        addBookForm.classList.add("hidden");
    };

    const cancelBtnEl = document.getElementById("cancelBtn");
    if (cancelBtnEl) cancelBtnEl.addEventListener("click", closeModal);

    const modalCancelBtnEl = document.getElementById("modalCancelBtn");
    if (modalCancelBtnEl) modalCancelBtnEl.addEventListener("click", closeModal);

// TOGGLE BETWEEN VIEW/EDIT MODES
// =====================

    const viewMode = document.getElementById("view-mode");
    const editMode = document.getElementById("edit-mode");
    const editItemBtn = document.getElementById("edititem-btn");
    const cancelEditBtn = document.getElementById("cancel-edit-btn")

    function showEditMode(){
        if(viewMode && editMode) {
            viewMode.classList.add("hidden");
            editMode.classList.remove("hidden");
            if (editItemBtn) editItemBtn.classList.add("hidden");//hide edit button while editing
        }
    }

    function showViewMode(){
        if(viewMode && editMode) {
            editMode.classList.add("hidden");
            viewMode.classList.remove("hidden");
            if(editItemBtn) editItemBtn.classList.remove("hidden");
        }
    }

    if (editItemBtn) {
        editItemBtn.addEventListener("click", (e) => {
            e.preventDefault();
            showEditMode();
        });
    }

    if (cancelEditBtn) {
        cancelEditBtn.addEventListener("click", (e) => {
            e.preventDefault();
            showViewMode();
        });
    }

    //Auto-open edit mode if URL contains ?mode=edit
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("mode") === "edit") {
        showEditMode();
    }
});

// =====================
// FETCH BOOK DETAILS
// =====================
async function fetchBookDetails(isbn) {
    const cleanIsbn = isbn.trim().replace(/[- ]/g, "");

    if (!cleanIsbn) return;

    try {
        const response = await fetch(`https://openlibrary.org/api/books?bibkeys=ISBN:${cleanIsbn}&format=json&jscmd=data`);
        const data = await response.json();
        const bookKey = `ISBN:${cleanIsbn}`;

        if (data[bookKey]) {
            const book = data[bookKey];

            // 1. Title & Author
            document.getElementById('title').value = book.title || '';
            
            if (book.authors && book.authors.length > 0) {
                document.getElementById('author').value = book.authors[0].name;
            } else {
                document.getElementById('author').value = '';
            }

            // 2. Cover image url (medium size)
            if(book.cover && book.cover.medium) {
                document.getElementById('cover_image').value = book.cover.medium;
            }

            // 3. Category
            if (book.subjects && book.subjects.length > 0) {
                document.getElementById('category').value = book.subjects[0].name;
            }

        } else {
            alert("Book found by ISBN, but details were not found on Open Library. Please fill in manually.");
        }
    } catch (error) {
        console.error("Error fetching book data:", error);
    }
}

// =====================
