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
                                echo "<script>alert('Successfully borrowed " . $quantity . " copy/copies of " . htmlspecialchars($book_title) . "!'); window.location.href='my_loans.php';</script>";
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

<div class="form-container">
    <h2>Borrow a Book</h2>
    <?php
    if (!empty($borrow_err)) {
        echo '<div class="alert alert-danger">' . $borrow_err . '</div>';
    }
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="book-search">Search Book:</label>
            <input type="text" id="book-search" placeholder="Type book title..." required>
            <input type="hidden" name="book_id" id="selected-book-id" class="<?php echo (!empty($book_id_err)) ? 'is-invalid' : ''; ?>">
            <div id="autocomplete-list" class="autocomplete-items"></div>
            <span class="invalid-feedback"><?php echo $book_id_err; ?></span>
        </div>

        <div class="form-group">
            <label for="borrow-quantity">Quantity:</label>
            <input type="number" name="quantity" id="borrow-quantity" min="1" class="form-control <?php echo (!empty($quantity_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $quantity; ?>" required>
            <span class="invalid-feedback"><?php echo $quantity_err; ?></span>
        </div>

        <div class="form-group">
            <label for="due-date">Due Date:</label>
            <input type="date" name="due_date" id="due-date" class="form-control <?php echo (!empty($due_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $due_date; ?>" required>
            <span class="invalid-feedback"><?php echo $due_date_err; ?></span>
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Borrow">
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("book-search");
        const autocompleteList = document.getElementById("autocomplete-list");
        const selectedBookIdInput = document.getElementById("selected-book-id");
        const allBooks = <?php echo json_encode($all_books); ?>;

        searchInput.addEventListener("input", function () {
            const query = searchInput.value.trim().toLowerCase();
            autocompleteList.innerHTML = "";
            selectedBookIdInput.value = ""; // Clear selected ID on new input

            if (query.length === 0) return;

            const suggestions = allBooks.filter(book =>
                book.title.toLowerCase().includes(query) && book.quantity_available > 0
            );

            suggestions.forEach(book => {
                const item = document.createElement("div");
                item.textContent = `${book.title} (Available: ${book.quantity_available})`;
                item.addEventListener("click", function () {
                    searchInput.value = book.title;
                    selectedBookIdInput.value = book.book_id;
                    autocompleteList.innerHTML = "";
                });
                autocompleteList.appendChild(item);
            });
        });

        // Close autocomplete when clicking outside
        document.addEventListener("click", function(e) {
            if (!searchInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                autocompleteList.innerHTML = "";
            }
        });
    });
</script>

<?php
require_once 'footer.php';
?>