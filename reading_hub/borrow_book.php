<?php
require_once 'header.php';

// Redirect if not a student
if (getUserRole() !== 'student') {
    header("Location: librarian_dashboard.php");
    exit();
}

$book_id = $quantity = $due_date = "";
$book_id_err = $quantity_err = $due_date_err = $borrow_err = "";
$student_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate book selection
    if (empty(trim($_POST["book_id"]))) {
        $book_id_err = "Please select a book.";
    } else {
        $book_id = trim($_POST["book_id"]);
    }

    // Validate quantity
    if (empty(trim($_POST["quantity"])) || !is_numeric($_POST["quantity"]) || $_POST["quantity"] < 1) {
        $quantity_err = "Please enter a valid quantity (at least 1).";
    } else {
        $quantity = trim($_POST["quantity"]);
    }

    // Validate due date
    if (empty(trim($_POST["due_date"]))) {
        $due_date_err = "Please select a due date.";
    } else {
        $due_date = trim($_POST["due_date"]);
        if (strtotime($due_date) < strtotime(date('Y-m-d'))) {
            $due_date_err = "Due date cannot be in the past.";
        }
    }

    // Check input errors before processing
    if (empty($book_id_err) && empty($quantity_err) && empty($due_date_err)) {
        // Check if student has outstanding penalties (simplified check)
        $penalty_check_sql = "SELECT COUNT(*) FROM penalties p JOIN loans l ON p.loan_id = l.loan_id WHERE l.student_id = ? AND p.status = 'pending'";
        if ($stmt_penalty = $conn->prepare($penalty_check_sql)) {
            $stmt_penalty->bind_param("i", $student_id);
            $stmt_penalty->execute();
            $stmt_penalty->bind_result($pending_penalties);
            $stmt_penalty->fetch();
            $stmt_penalty->close();

            if ($pending_penalties > 0) {
                $borrow_err = "You have outstanding penalties. Please clear them before borrowing new books.";
            }
        }

        if (empty($borrow_err)) {
            // Check book availability
            $sql_check_qty = "SELECT title, quantity_available FROM books WHERE book_id = ?";
            if ($stmt_check_qty = $conn->prepare($sql_check_qty)) {
                $stmt_check_qty->bind_param("i", $book_id);
                $stmt_check_qty->execute();
                $stmt_check_qty->bind_result($book_title, $available_qty);
                $stmt_check_qty->fetch();
                $stmt_check_qty->close();

                if ($available_qty >= $quantity) {
                    // Update book quantity
                    $sql_update_book = "UPDATE books SET quantity_available = quantity_available - ? WHERE book_id = ?";
                    if ($stmt_update_book = $conn->prepare($sql_update_book)) {
                        $stmt_update_book->bind_param("ii", $quantity, $book_id);
                        $stmt_update_book->execute();
                        $stmt_update_book->close();

                        // Record the loan
                        $sql_insert_loan = "INSERT INTO loans (student_id, book_id, borrow_date, due_date, status) VALUES (?, ?, CURDATE(), ?, 'borrowed')";
                        if ($stmt_insert_loan = $conn->prepare($sql_insert_loan)) {
                            $stmt_insert_loan->bind_param("iis", $student_id, $book_id, $due_date);
                            if ($stmt_insert_loan->execute()) {
                                logAudit($student_id, 'borrow_book', $book_id, 'Borrowed ' . $quantity . ' copies of ' . $book_title);
                                echo "<script>
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Book Borrowed Successfully!',
                                        html: '<div style=\"text-align: left;\"><h3 style=\"color: #10b981; margin-bottom: 15px;\'>🎉 Borrowing Confirmed!</h3><p><strong>Book:</strong> " . htmlspecialchars($book_title) . "</p><p><strong>Quantity:</strong> " . $quantity . " copy/copies</p><p><strong>Due Date:</strong> " . $due_date . "</p><p style=\"margin-top: 15px; padding: 10px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #10b981;\"><i data-lucide=\"info\"></i> Please return the book on or before the due date to avoid penalties.</p></div>',
                                        confirmButtonText: 'View My Loans',
                                        showCancelButton: true,
                                        cancelButtonText: 'Borrow Another',
                                        confirmButtonColor: '#10b981',
                                        cancelButtonColor: '#6b7280',
                                        background: 'linear-gradient(135deg, #f0fdf4, #d1fae5)',
                                        customClass: {
                                            popup: 'animated pulse'
                                        }
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = 'my_loans.php';
                                        } else {
                                            // Reset form
                                            document.getElementById('book-search').value = '';
                                            document.getElementById('selected-book-id').value = '';
                                            document.getElementById('borrow-quantity').value = '1';
                                            document.getElementById('due-date').value = '';
                                            document.getElementById('book-preview').classList.remove('active');
                                            updateFormSteps(1);
                                        }
                                    });
                                </script>";
                            } else {
                                $borrow_err = "Error recording loan: " . $stmt_insert_loan->error;
                            }
                            $stmt_insert_loan->close();
                        }
                    } else {
                        $borrow_err = "Error updating book quantity: " . $stmt_update_book->error;
                    }
                } else {
                    $borrow_err = "Not enough copies available. Only " . $available_qty . " left.";
                }
            }
        }
    }
}

// Fetch all books for the autocomplete/dropdown
$all_books = $conn->query("SELECT book_id, title, quantity_available FROM books WHERE quantity_available > 0 ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);
?>

<link rel="stylesheet" href="borrow.css">

<div class="borrow-container">
    <div class="borrow-content">
        <div class="borrow-header">
            <i data-lucide="library-big" class="header-icon"></i>
            <h2>Borrow a Book</h2>
            <p>Discover and borrow from our extensive collection of books</p>
        </div>

        <div class="borrow-form">
            <!-- Progress Steps -->
            <div class="form-steps">
                <div class="step active" id="step-1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Select Book</div>
                </div>
                <div class="step" id="step-2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Choose Quantity</div>
                </div>
                <div class="step" id="step-3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Set Due Date</div>
                </div>
                <div class="step" id="step-4">
                    <div class="step-circle">4</div>
                    <div class="step-label">Confirm</div>
                </div>
            </div>

            <?php if (!empty($borrow_err)): ?>
                <div class="alert alert-danger">
                    <i data-lucide="alert-triangle" class="alert-icon"></i>
                    <?php echo $borrow_err; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="borrowForm">
                <!-- Book Search -->
                <div class="form-group">
                    <label for="book-search" class="form-label">
                        <i data-lucide="search" class="form-label-icon"></i>
                        Search Book
                    </label>
                    <div class="autocomplete-container">
                        <input type="text" id="book-search" placeholder="Type book title to search..." class="form-input <?php echo (!empty($book_id_err)) ? 'is-invalid' : ''; ?>" required>
                        <input type="hidden" name="book_id" id="selected-book-id" class="<?php echo (!empty($book_id_err)) ? 'is-invalid' : ''; ?>">
                        <div id="autocomplete-list" class="autocomplete-items"></div>
                        <span class="invalid-feedback">
                            <i data-lucide="alert-circle"></i>
                            <?php echo $book_id_err; ?>
                        </span>
                    </div>
                    
                    <!-- Book Preview -->
                    <div id="book-preview" class="book-preview">
                        <div class="preview-title" id="preview-title"></div>
                        <div class="preview-meta">
                            <span id="preview-availability">
                                <i data-lucide="package" class="meta-icon"></i>
                                <span id="availability-text"></span>
                            </span>
                            <span id="preview-id">
                                <i data-lucide="hash" class="meta-icon"></i>
                                <span id="id-text"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="form-group">
                    <label class="form-label">
                        <i data-lucide="package" class="form-label-icon"></i>
                        Quantity
                    </label>
                    <div class="quantity-container">
                        <button type="button" class="quantity-btn" id="decrease-qty">-</button>
                        <input type="number" name="quantity" id="borrow-quantity" min="1" max="10" class="form-input quantity-input <?php echo (!empty($quantity_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $quantity ?: '1'; ?>" required>
                        <button type="button" class="quantity-btn" id="increase-qty">+</button>
                    </div>
                    <span class="invalid-feedback">
                        <i data-lucide="alert-circle"></i>
                        <?php echo $quantity_err; ?>
                    </span>
                </div>

                <!-- Due Date -->
                <div class="form-group">
                    <label for="due-date" class="form-label">
                        <i data-lucide="calendar" class="form-label-icon"></i>
                        Due Date
                    </label>
                    <div class="date-input-container">
                        <input type="date" name="due_date" id="due-date" class="form-input <?php echo (!empty($due_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $due_date; ?>" required>
                        <i data-lucide="calendar" class="calendar-icon"></i>
                    </div>
                    <span class="invalid-feedback">
                        <i data-lucide="alert-circle"></i>
                        <?php echo $due_date_err; ?>
                    </span>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="btn-submit" id="submit-btn">
                        <i data-lucide="book-check" class="btn-icon"></i>
                        Borrow Book Now
                    </button>
                </div>
            </form>

            <!-- Feature Cards -->
            <div class="feature-cards">
                <div class="feature-card">
                    <i data-lucide="shield-check" class="feature-icon"></i>
                    <h4>Secure Process</h4>
                    <p>Your borrowing history is securely recorded and managed</p>
                </div>
                <div class="feature-card">
                    <i data-lucide="clock" class="feature-icon"></i>
                    <h4>Flexible Duration</h4>
                    <p>Choose your preferred due date for maximum convenience</p>
                </div>
                <div class="feature-card">
                    <i data-lucide="bell" class="feature-icon"></i>
                    <h4>Smart Reminders</h4>
                    <p>Get notified before your books are due for return</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("book-search");
        const autocompleteList = document.getElementById("autocomplete-list");
        const selectedBookIdInput = document.getElementById("selected-book-id");
        const bookPreview = document.getElementById("book-preview");
        const previewTitle = document.getElementById("preview-title");
        const availabilityText = document.getElementById("availability-text");
        const idText = document.getElementById("id-text");
        const quantityInput = document.getElementById("borrow-quantity");
        const decreaseBtn = document.getElementById("decrease-qty");
        const increaseBtn = document.getElementById("increase-qty");
        const submitBtn = document.getElementById("submit-btn");
        const allBooks = <?php echo json_encode($all_books); ?>;

        // Set minimum due date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('due-date').min = tomorrow.toISOString().split('T')[0];

        // Update form steps based on user progress
        function updateFormSteps(step) {
            document.querySelectorAll('.step').forEach((s, index) => {
                if (index < step) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        }

        // Book search functionality
        searchInput.addEventListener("input", function () {
            const query = searchInput.value.trim().toLowerCase();
            autocompleteList.innerHTML = "";
            selectedBookIdInput.value = "";
            bookPreview.classList.remove("active");
            updateFormSteps(1);

            if (query.length < 2) return;

            const suggestions = allBooks.filter(book =>
                book.title.toLowerCase().includes(query) && book.quantity_available > 0
            );

            if (suggestions.length === 0) {
                const noResults = document.createElement("div");
                noResults.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: #64748b;">
                        <i data-lucide="book-x" style="width: 32px; height: 32px; margin-bottom: 8px;"></i>
                        <p style="margin: 0; font-weight: 500;">No books found</p>
                        <p style="margin: 4px 0 0 0; font-size: 0.9rem;">Try searching with different keywords</p>
                    </div>
                `;
                autocompleteList.appendChild(noResults);
                return;
            }

            suggestions.forEach(book => {
                const item = document.createElement("div");
                item.className = "book-suggestion";
                item.innerHTML = `
                    <span class="book-title">${book.title}</span>
                    <span class="book-availability">${book.quantity_available} available</span>
                `;
                item.addEventListener("click", function () {
                    searchInput.value = book.title;
                    selectedBookIdInput.value = book.book_id;
                    autocompleteList.innerHTML = "";
                    
                    // Show book preview
                    previewTitle.textContent = book.title;
                    availabilityText.textContent = `${book.quantity_available} copies available`;
                    idText.textContent = `ID: ${book.book_id}`;
                    bookPreview.classList.add("active");
                    
                    // Update quantity max and form steps
                    quantityInput.max = book.quantity_available;
                    updateQuantityButtons();
                    updateFormSteps(2);
                    
                    // Animate the preview
                    bookPreview.style.animation = 'none';
                    setTimeout(() => {
                        bookPreview.style.animation = 'slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                    }, 10);
                });
                autocompleteList.appendChild(item);
            });
        });

        // Quantity controls
        function updateQuantityButtons() {
            const currentQty = parseInt(quantityInput.value);
            const maxQty = parseInt(quantityInput.max);
            decreaseBtn.disabled = currentQty <= 1;
            increaseBtn.disabled = currentQty >= maxQty;
            
            if (currentQty > 0) {
                updateFormSteps(3);
            }
        }

        decreaseBtn.addEventListener("click", function() {
            let currentQty = parseInt(quantityInput.value);
            if (currentQty > 1) {
                quantityInput.value = currentQty - 1;
                updateQuantityButtons();
            }
        });

        increaseBtn.addEventListener("click", function() {
            let currentQty = parseInt(quantityInput.value);
            const maxQty = parseInt(quantityInput.max);
            if (currentQty < maxQty) {
                quantityInput.value = currentQty + 1;
                updateQuantityButtons();
            }
        });

        quantityInput.addEventListener("input", updateQuantityButtons);
        updateQuantityButtons();

        // Due date change
        document.getElementById('due-date').addEventListener('change', function() {
            if (this.value) {
                updateFormSteps(4);
            }
        });

        // Close autocomplete when clicking outside
        document.addEventListener("click", function(e) {
            if (!searchInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                autocompleteList.innerHTML = "";
            }
        });

        // Form submission with loading state
        document.getElementById("borrowForm").addEventListener("submit", function(e) {
            if (!selectedBookIdInput.value) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Book Not Selected',
                    text: 'Please select a book from the search results',
                    confirmButtonColor: '#6366f1'
                });
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="loading-spinner"></div> Processing Your Request...';
        });

        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<?php
require_once 'footer.php';
?>
