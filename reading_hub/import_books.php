<?php
require_once 'config.php';  // Include your DB config
require_once 'functions.php';  // If needed for any helper functions

// Path to your JSON file
$jsonFile = 'books.json';

// Check if file exists
if (!file_exists($jsonFile)) {
    die("Error: $jsonFile not found.\n");
}

// Read and decode JSON
$jsonData = file_get_contents($jsonFile);
$books = json_decode($jsonData, true);

if ($books === null) {
    die("Error: Invalid JSON format in $jsonFile.\n");
}

// Connect to DB
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

$importedCount = 0;
$skippedCount = 0;
$errors = [];

// Loop through each book
foreach ($books as $book) {
    // Extract and map fields from JSON
    $title = trim($book['title'] ?? '');
    $authorName = trim($book['author'] ?? '');
    $genreName = trim($book['category'] ?? '');  // Map 'category' to genre
    $yearLevel = trim($book['year_level'] ?? null);
    $illustrator = trim($book['illustrator'] ?? null);
    $quantity = (int)($book['quantity'] ?? 1);  // Default to 1 if missing
    $dateAdded = date('Y-m-d');  // Default to today since not in JSON

    // Handle multiple authors: Take the first one (split by comma)
    if (strpos($authorName, ',') !== false) {
        $authorName = trim(explode(',', $authorName)[0]);
    }

    // Validate required fields
    if (empty($title) || empty($authorName) || empty($genreName)) {
        $errors[] = "Skipping book: Missing title, author, or category for '$title'";
        $skippedCount++;
        continue;
    }

    // Skip books with zero or negative quantity (to avoid constraint violation)
    if ($quantity <= 0) {
        $errors[] = "Skipping book: Zero or negative quantity for '$title' (quantity: $quantity)";
        $skippedCount++;
        continue;
    }

    // Check/Insert Author
    $authorId = getOrInsertAuthor($conn, $authorName);
    if (!$authorId) {
        $errors[] = "Failed to insert author: $authorName";
        $skippedCount++;
        continue;
    }

    // Check/Insert Genre (using 'category' as genre_name)
    $genreId = getOrInsertGenre($conn, $genreName);
    if (!$genreId) {
        $errors[] = "Failed to insert genre: $genreName";
        $skippedCount++;
        continue;
    }

    // Insert Book (set quantity_total and quantity_available to 'quantity')
    $sql = "INSERT INTO books (title, author_id, genre_id, year_level, illustrator, quantity_total, quantity_available, date_added) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity_total = quantity_total + VALUES(quantity_total), quantity_available = quantity_available + VALUES(quantity_available)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siississ", $title, $authorId, $genreId, $yearLevel, $illustrator, $quantity, $quantity, $dateAdded);
    
    if ($stmt->execute()) {
        $importedCount++;
    } else {
        $errors[] = "Failed to insert book: $title - " . $stmt->error;
        $skippedCount++;
    }
    $stmt->close();
}

// Helper Functions (unchanged)
function getOrInsertAuthor($conn, $authorName) {
    // Check if author exists
    $sql = "SELECT author_id FROM authors WHERE author_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $authorName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['author_id'];
    }
    $stmt->close();

    // Insert new author with default biography
    $defaultBiography = "Biography not available.";  // Default value
    $sql = "INSERT INTO authors (author_name, biography) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $authorName, $defaultBiography);
    if ($stmt->execute()) {
        $authorId = $stmt->insert_id;
        $stmt->close();
        return $authorId;
    }
    $stmt->close();
    return false;
}

function getOrInsertGenre($conn, $genreName) {
    // Check if genre exists
    $sql = "SELECT genre_id FROM genres WHERE genre_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $genreName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['genre_id'];
    }
    $stmt->close();

    // Insert new genre
    $sql = "INSERT INTO genres (genre_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $genreName);
    if ($stmt->execute()) {
        $genreId = $stmt->insert_id;
        $stmt->close();
        return $genreId;
    }
    $stmt->close();
    return false;
}

// Output Results
echo "Import Complete!\n";
echo "Books imported: $importedCount\n";
echo "Books skipped: $skippedCount\n";
if (!empty($errors)) {
    echo "Details:\n" . implode("\n", $errors) . "\n";
}

// Close DB
$conn->close();
?>