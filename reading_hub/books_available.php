<?php
require_once 'header.php';

$books = [];
$sql = "SELECT b.book_id, b.title, a.author_name, b.year_level, g.genre_name, b.quantity_available, b.illustrator
        FROM books b
        LEFT JOIN authors a ON b.author_id = a.author_id
        LEFT JOIN genres g ON b.genre_id = g.genre_id
        WHERE b.quantity_available > 0
        ORDER BY b.title ASC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $result->free();
} else {
    echo "Error: " . $conn->error;
}
?>

<div class="table-container">
    <h2>Books Available</h2>
    <table>
        <thead>
            <tr>
                <th>Book Title</th>
                <th>Author</th>
                <th>Year Level</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Illustrator</th>
            </tr>
        </thead>
        <tbody id="books-list">
            <?php if (!empty($books)): ?>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($book['year_level'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($book['genre_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($book['quantity_available']); ?></td>
                        <td><?php echo htmlspecialchars($book['illustrator'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan='6'>No books currently available.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'footer.php';
?>