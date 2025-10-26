<?php
// edit_author.php
require_once 'header.php';

if (getUserRole() !== 'librarian') {
    header("Location: student_dashboard.php");
    exit();
}

$author_id = $_GET['id'] ?? null;
if (!$author_id) {
    header("Location: authors.php");
    exit();
}

// Fetch author data
$sql = "SELECT author_id, author_name, biography FROM authors WHERE author_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $author_id);
$stmt->execute();
$result = $stmt->get_result();
$author = $result->fetch_assoc();
$stmt->close();

if (!$author) {
    header("Location: authors.php");
    exit();
}

// Handle form submission for editing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Similar to add author but with UPDATE query
    // Implementation would be similar to the add functionality
}

// The rest would be similar to add_book.php but with edit functionality
?>