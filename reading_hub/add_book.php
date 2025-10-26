<?php
require_once 'header.php';

// Redirect if not a librarian
if (getUserRole() !== 'librarian') {
    header("Location: student_dashboard.php");
    exit();
}

$title = $author_id = $genre_id = $year_level = $illustrator = $quantity_total = "";
$title_err = $author_err = $genre_err = $quantity_err = "";

// Fetch authors and genres for dropdowns
$authors = $conn->query("SELECT author_id, author_name FROM authors ORDER BY author_name")->fetch_all(MYSQLI_ASSOC);
$genres = $conn->query("SELECT genre_id, genre_name FROM genres ORDER BY genre_name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a book title.";
    } else {
        $title = trim($_POST["title"]);
    }

    if (empty(trim($_POST["author_id"]))) {
        $author_err = "Please select an author.";
    } else {
        $author_id = trim($_POST["author_id"]);
    }

    if (empty(trim($_POST["genre_id"]))) {
        $genre_err = "Please select a genre.";
    } else {
        $genre_id = trim($_POST["genre_id"]);
    }

    if (empty(trim($_POST["quantity_total"])) || !is_numeric($_POST["quantity_total"]) || $_POST["quantity_total"] < 1) {
        $quantity_err = "Please enter a valid quantity (at least 1).";
    } else {
        $quantity_total = trim($_POST["quantity_total"]);
    }

    $year_level = trim($_POST["year_level"]);
    $illustrator = trim($_POST["illustrator"]);

    // Check input errors before inserting in database
    if (empty($title_err) && empty($author_err) && empty($genre_err) && empty($quantity_err)) {
        $sql = "INSERT INTO books (title, author_id, genre_id, year_level, illustrator, quantity_total, quantity_available, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("siissii", $param_title, $param_author_id, $param_genre_id, $param_year_level, $param_illustrator, $param_quantity_total, $param_quantity_available);

            $param_title = $title;
            $param_author_id = $author_id;
            $param_genre_id = $genre_id;
            $param_year_level = $year_level;
            $param_illustrator = $illustrator;
            $param_quantity_total = $quantity_total;
            $param_quantity_available = $quantity_total; // Initially, all are available

            if ($stmt->execute()) {
                logAudit($_SESSION['user_id'], 'add_book', $conn->insert_id, 'Added new book: ' . $title);
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Book Added Successfully!',
                        html: '<div style=\"text-align: left;\"><h3 style=\"color: #10b981; margin-bottom: 15px;\'>📚 Book Added!</h3><p><strong>Title:</strong> " . htmlspecialchars($title) . "</p><p><strong>Quantity:</strong> " . $quantity_total . " copies</p><p style=\"margin-top: 15px; padding: 10px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #10b981;\"><i data-lucide=\"info\"></i> The book has been added to the library collection.</p></div>',
                        confirmButtonText: 'Add Another Book',
                        showCancelButton: true,
                        cancelButtonText: 'View Books',
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280',
                        background: 'linear-gradient(135deg, #f0fdf4, #d1fae5)',
                        customClass: {
                            popup: 'animated pulse'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Reset form
                            document.querySelector('form').reset();
                        } else {
                            window.location.href = 'books_available.php';
                        }
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to add book. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                </script>";
            }
            $stmt->close();
        }
    }
}
?>

<link rel="stylesheet" href="add_book.css">

<div class="add-book-container">
    <div class="add-book-content">
        <div class="add-book-header">
            <i data-lucide="book-plus" class="header-icon"></i>
            <h2>Add New Book</h2>
            <p>Expand the library collection by adding new books</p>
        </div>

        <div class="add-book-form">
            <?php if (!empty($borrow_err)): ?>
                <div class="alert alert-danger">
                    <i data-lucide="alert-triangle" class="alert-icon"></i>
                    <?php echo $borrow_err; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="addBookForm">
                <!-- Book Title -->
                <div class="form-group">
                    <label class="form-label">
                        <i data-lucide="book-open" class="form-label-icon"></i>
                        Book Title
                    </label>
                    <input type="text" name="title" class="form-input <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>" placeholder="Enter book title" required>
                    <span class="invalid-feedback">
                        <i data-lucide="alert-circle"></i>
                        <?php echo $title_err; ?>
                    </span>
                </div>

                <!-- Author Selection -->
                <div class="form-group">
                    <label class="form-label">
                        <i data-lucide="user" class="form-label-icon"></i>
                        Author
                    </label>
                    <select name="author_id" class="form-input <?php echo (!empty($author_err)) ? 'is-invalid' : ''; ?>" required>
                        <option value="">Select Author</option>
                        <?php foreach ($authors as $author): ?>
                            <option value="<?php echo $author['author_id']; ?>" <?php echo ($author_id == $author['author_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($author['author_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="invalid-feedback">
                        <i data-lucide="alert-circle"></i>
                        <?php echo $author_err; ?>
                    </span>
                    <small class="form-help">Don't see the author? <a href="authors.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Add Author</a>.</small>
                </div>

                <!-- Genre Selection -->
                <div class="form-group">
                    <label class="form-label">
                        <i data-lucide="tag" class="form-label-icon"></i>
                        Category/Genre
                    </label>
                    <select name="genre_id" class="form-input <?php echo (!empty($genre_err)) ? 'is-invalid' : ''; ?>" required>
                        <option value="">Select Genre</option>
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?php echo $genre['genre_id']; ?>" <?php echo ($genre_id == $genre['genre_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($genre['genre_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="invalid-feedback">
                        <i data-lucide="alert-circle"></i>
                        <?php echo $genre_err; ?>
                    </span>
                </div>

                <!-- Year Level and Illustrator -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i data-lucide="graduation-cap" class="form-label-icon"></i>
                            Year Level
                        </label>
                        <input type="text" name="year_level" class="form-input" value="<?php echo $year_level; ?>" placeholder="e.g., K, P, 1, 2">
                        <small class="form-help">Optional - for children's books</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i data-lucide="palette" class="form-label-icon"></i>
                            Illustrator
                        </label>
                        <input type="text" name="illustrator" class="form-input" value="<?php echo $illustrator; ?>" placeholder="Enter illustrator name">
                        <small class="form-help">Optional - for illustrated books</small>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="form-group">
                    <label class="form-label">
                        <i data-lucide="package" class="form-label-icon"></i>
                        Total Quantity
                    </label>
                    <div class="quantity-container">
                        <button type="button" class="quantity-btn" id="decrease-qty">-</button>
                        <input type="number" name="quantity_total" id="quantity-total" min="1" max="100" class="form-input quantity-input <?php echo (!empty($quantity_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $quantity_total ?: '1'; ?>" required>
                        <button type="button" class="quantity-btn" id="increase-qty">+</button>
                    </div>
                    <span class="invalid-feedback">
                        <i data-lucide="alert-circle"></i>
                        <?php echo $quantity_err; ?>
                    </span>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="btn-submit" id="submit-btn">
                        <i data-lucide="plus-circle" class="btn-icon"></i>
                        Add Book to Library
                    </button>
                </div>
            </form>

            <!-- Feature Cards -->
            <div class="feature-cards">
                <div class="feature-card">
                    <i data-lucide="library" class="feature-icon"></i>
                    <h4>Expand Collection</h4>
                    <p>Grow the library's resources with new titles</p>
                </div>
                <div class="feature-card">
                    <i data-lucide="search" class="feature-icon"></i>
                    <h4>Easy Cataloging</h4>
                    <p>Quickly add books with our streamlined form</p>
                </div>
                <div class="feature-card">
                    <i data-lucide="users" class="feature-icon"></i>
                    <h4>Student Access</h4>
                    <p>New books become immediately available for borrowing</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const quantityInput = document.getElementById("quantity-total");
        const decreaseBtn = document.getElementById("decrease-qty");
        const increaseBtn = document.getElementById("increase-qty");
        const submitBtn = document.getElementById("submit-btn");

        // Quantity controls
        function updateQuantityButtons() {
            const currentQty = parseInt(quantityInput.value);
            decreaseBtn.disabled = currentQty <= 1;
            increaseBtn.disabled = currentQty >= 100;
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
            if (currentQty < 100) {
                quantityInput.value = currentQty + 1;
                updateQuantityButtons();
            }
        });

        quantityInput.addEventListener("input", updateQuantityButtons);
        updateQuantityButtons();

        // Form submission with loading state
        document.getElementById("addBookForm").addEventListener("submit", function(e) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="loading-spinner"></div> Adding Book to Library...';
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