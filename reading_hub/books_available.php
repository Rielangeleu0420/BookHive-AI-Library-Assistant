<?php
require_once 'header.php';

// Fetch available books from DB
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

<div class="books-page">
    <!-- Search and Filter Bar -->
    <div class="search-filter-container">
        <input type="text" id="searchInput" placeholder="Search by title, author, or keywords 🔍" onkeyup="filterBooks()">
        <div class="filter-dropdowns">
            <select id="filterGenre" onchange="filterBooks()">
                <option value="">All Categories</option>
                <?php
                $genreResult = $conn->query("SELECT genre_name FROM genres ORDER BY genre_name ASC");
                while ($genre = $genreResult->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($genre['genre_name']) . '">' . htmlspecialchars($genre['genre_name']) . '</option>';
                }
                ?>
            </select>
            <select id="filterAuthor" onchange="filterBooks()">
                <option value="">All Authors</option>
                <?php
                $authorResult = $conn->query("SELECT author_name FROM authors ORDER BY author_name ASC");
                while ($author = $authorResult->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($author['author_name']) . '">' . htmlspecialchars($author['author_name']) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <!-- Books Grid -->
    <h2 class="section-title">📚 Books Available</h2>
    <div id="booksGrid" class="books-grid">
        <?php if (!empty($books)): ?>
            <?php foreach ($books as $book): ?>
                <div class="book-card" 
                     data-title="<?php echo strtolower($book['title']); ?>" 
                     data-author="<?php echo strtolower($book['author_name']); ?>" 
                     data-genre="<?php echo strtolower($book['genre_name']); ?>">
                    <div class="book-header">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <span class="availability <?php echo $book['quantity_available'] > 0 ? 'available' : 'unavailable'; ?>">
                            <?php echo $book['quantity_available'] > 0 ? $book['quantity_available'] . ' available' : 'Checked out'; ?>
                        </span>
                    </div>
                    <p class="author"><?php echo htmlspecialchars($book['author_name'] ?? 'N/A'); ?></p>
                    <div class="book-cover">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <p class="desc">A great read for <?php echo htmlspecialchars($book['year_level'] ?? 'students'); ?>.</p>
                    <div class="book-footer">
                        <span class="genre-tag"><?php echo htmlspecialchars($book['genre_name'] ?? 'General'); ?></span>
                        <span class="illustrator"><?php echo htmlspecialchars($book['illustrator'] ?? '-'); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-books">No books currently available.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Floating AI Chat Button -->
<div class="chat-button-container">
  <a href="AIChat.php" class="chat-btn">💬 AI Chat</a>
</div>

<script>
function filterBooks() {
  const searchInput = document.getElementById("searchInput").value.toLowerCase();
  const filterGenre = document.getElementById("filterGenre").value.toLowerCase();
  const filterAuthor = document.getElementById("filterAuthor").value.toLowerCase();
  const books = document.querySelectorAll(".book-card");

  books.forEach(book => {
    const title = book.dataset.title;
    const author = book.dataset.author;
    const genre = book.dataset.genre;
    const match = 
      (title.includes(searchInput) || author.includes(searchInput)) &&
      (filterGenre === "" || genre === filterGenre) &&
      (filterAuthor === "" || author === filterAuthor);
    book.style.display = match ? "block" : "none";
  });
}
</script>

<?php require_once 'footer.php'; ?>
