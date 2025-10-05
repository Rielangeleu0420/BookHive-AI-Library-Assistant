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
                echo "<script>alert('Book added successfully!'); window.location.href='books_available.php';</script>";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<div class="form-container">
    <h2>Add New Book</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Book Title</label>
            <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
            <span class="invalid-feedback"><?php echo $title_err; ?></span>
        </div>
        <div class="form-group">
            <label>Author</label>
            <select name="author_id" class="form-control <?php echo (!empty($author_err)) ? 'is-invalid' : ''; ?>">
                <option value="">Select Author</option>
                <?php foreach ($authors as $author): ?>
                    <option value="<?php echo $author['author_id']; ?>" <?php echo ($author_id == $author['author_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($author['author_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="invalid-feedback"><?php echo $author_err; ?></span>
            <small>Don't see the author? Add them via User Management (placeholder).</small>
        </div>
        <div class="form-group">
            <label>Category/Genre</label>
            <select name="genre_id" class="form-control <?php echo (!empty($genre_err)) ? 'is-invalid' : ''; ?>">
                <option value="">Select Genre</option>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?php echo $genre['genre_id']; ?>" <?php echo ($genre_id == $genre['genre_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($genre['genre_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="invalid-feedback"><?php echo $genre_err; ?></span>
        </div>
        <div class="form-group">
            <label>Year Level (e.g., K, P, 1, 2)</label>
            <input type="text" name="year_level" class="form-control" value="<?php echo $year_level; ?>">
        </div>
        <div class="form-group">
            <label>Illustrator</label>
            <input type="text" name="illustrator" class="form-control" value="<?php echo $illustrator; ?>">
        </div>
        <div class="form-group">
            <label>Total Quantity</label>
            <input type="number" name="quantity_total" class="form-control <?php echo (!empty($quantity_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $quantity_total; ?>" min="1">
            <span class="invalid-feedback"><?php echo $quantity_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Add Book">
        </div>
    </form>
</div>

<?php
require_once 'footer.php';
?>