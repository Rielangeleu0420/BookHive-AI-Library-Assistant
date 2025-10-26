<?php
// authors.php
require_once 'header.php';

// Redirect if not a librarian
if (getUserRole() !== 'librarian') {
    header("Location: student_dashboard.php");
    exit();
}

$author_name = $biography = "";
$author_name_err = $biography_err = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate author name
    if (empty(trim($_POST["author_name"]))) {
        $author_name_err = "Please enter an author name.";
    } else {
        $author_name = trim($_POST["author_name"]);
        
        // Check if author already exists
        $check_sql = "SELECT author_id FROM authors WHERE author_name = ?";
        if ($stmt = $conn->prepare($check_sql)) {
            $stmt->bind_param("s", $param_author_name);
            $param_author_name = $author_name;
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $author_name_err = "This author already exists in the database.";
                }
            }
            $stmt->close();
        }
    }

    // Validate biography (optional)
    $biography = trim($_POST["biography"]);

    // Check input errors before inserting in database
    if (empty($author_name_err) && empty($biography_err)) {
        $sql = "INSERT INTO authors (author_name, biography) VALUES (?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $param_author_name, $param_biography);

            $param_author_name = $author_name;
            $param_biography = $biography;

            if ($stmt->execute()) {
                logAudit($_SESSION['user_id'], 'add_author', $conn->insert_id, 'Added new author: ' . $author_name);
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Author Added Successfully!',
                        html: '<div style=\"text-align: left;\"><h3 style=\"color: #10b981; margin-bottom: 15px;\">✍️ Author Added!</h3><p><strong>Name:</strong> " . htmlspecialchars($author_name) . "</p><p style=\"margin-top: 15px; padding: 10px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #10b981;\"><i data-lucide=\"info\"></i> The author has been added to the library database.</p></div>',
                        confirmButtonText: 'Add Another Author',
                        showCancelButton: true,
                        cancelButtonText: 'View Authors',
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
                            window.location.href = 'authors.php';
                        }
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to add author. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                </script>";
            }
            $stmt->close();
        }
    }
}

// Fetch all authors for the table
$authors = $conn->query("SELECT author_id, author_name, biography, created_at FROM authors ORDER BY author_name")->fetch_all(MYSQLI_ASSOC);
?>

<link rel="stylesheet" href="authors.css">

<div class="authors-container">
    <div class="authors-content">
        <!-- Header Section -->
        <div class="authors-header">
            <div class="header-content">
                <i data-lucide="users" class="header-icon"></i>
                <div class="header-text">
                    <h1>Manage Authors</h1>
                    <p>Add and manage authors in the library collection</p>
                </div>
            </div>
        </div>

        <div class="authors-layout">
            <!-- Add Author Form Section -->
            <div class="form-section">
                <div class="form-card">
                    <div class="form-header">
                        <h2>Add New Author</h2>
                        <p>Fill in the details to add a new author</p>
                    </div>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="addAuthorForm" class="author-form">
                        <!-- Author Name -->
                        <div class="form-group">
                            <label class="form-label">
                                <i data-lucide="user" class="form-label-icon"></i>
                                Author Name
                            </label>
                            <input type="text" name="author_name" class="form-input <?php echo (!empty($author_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $author_name; ?>" placeholder="Enter author's full name" required>
                            <span class="invalid-feedback">
                                <i data-lucide="alert-circle"></i>
                                <?php echo $author_name_err; ?>
                            </span>
                        </div>

                        <!-- Biography -->
                        <div class="form-group">
                            <label class="form-label">
                                <i data-lucide="file-text" class="form-label-icon"></i>
                                Biography
                            </label>
                            <textarea name="biography" class="form-textarea" rows="4" placeholder="Enter author biography (optional)"><?php echo $biography; ?></textarea>
                            <div class="form-help">
                                <i data-lucide="info" class="help-icon"></i>
                                Optional - Add details about the author's background and works
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-submit" id="submit-btn">
                                <i data-lucide="user-plus" class="btn-icon"></i>
                                Add Author to Library
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Feature Cards -->
                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="feature-icon-container">
                            <i data-lucide="book-open" class="feature-icon"></i>
                        </div>
                        <h4>Organize Collection</h4>
                        <p>Maintain a well-structured author database</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon-container">
                            <i data-lucide="search" class="feature-icon"></i>
                        </div>
                        <h4>Easy Search</h4>
                        <p>Quickly find authors when adding new books</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon-container">
                            <i data-lucide="database" class="feature-icon"></i>
                        </div>
                        <h4>Complete Records</h4>
                        <p>Store detailed author information and biographies</p>
                    </div>
                </div>
            </div>

            <!-- Authors List Section -->
            <div class="list-section">
                <div class="list-header">
                    <div class="list-title">
                        <h3>All Authors (<?php echo count($authors); ?>)</h3>
                        <p>Browse and manage existing authors</p>
                    </div>
                    <div class="list-actions">
                        <div class="search-box">
                            <i data-lucide="search" class="search-icon"></i>
                            <input type="text" placeholder="Search authors..." class="search-input">
                        </div>
                    </div>
                </div>

                <div class="authors-grid">
                    <?php if (count($authors) > 0): ?>
                        <?php foreach ($authors as $author): ?>
                            <div class="author-card">
                                <div class="author-header">
                                    <div class="author-avatar">
                                        <i data-lucide="user" class="avatar-icon"></i>
                                    </div>
                                    <div class="author-info">
                                        <h4 class="author-name"><?php echo htmlspecialchars($author['author_name']); ?></h4>
                                        <span class="author-id">ID: <?php echo $author['author_id']; ?></span>
                                    </div>
                                </div>

                                <div class="author-details">
                                    <div class="detail-item">
                                        <i data-lucide="file-text" class="detail-icon"></i>
                                        <div class="detail-content">
                                            <span class="detail-label">Biography</span>
                                            <span class="detail-value">
                                                <?php 
                                                if (!empty($author['biography'])) {
                                                    echo strlen($author['biography']) > 100 
                                                        ? htmlspecialchars(substr($author['biography'], 0, 100)) . '...' 
                                                        : htmlspecialchars($author['biography']);
                                                } else {
                                                    echo '<span class="no-info">Biography not available</span>';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <i data-lucide="calendar" class="detail-icon"></i>
                                        <div class="detail-content">
                                            <span class="detail-label">Date Added</span>
                                            <span class="detail-value"><?php echo date('M j, Y', strtotime($author['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="author-actions">
                                    <button class="btn-action btn-edit" onclick="editAuthor(<?php echo $author['author_id']; ?>)">
                                        <i data-lucide="edit" class="action-icon"></i>
                                        Edit
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteAuthor(<?php echo $author['author_id']; ?>, '<?php echo htmlspecialchars($author['author_name']); ?>')">
                                        <i data-lucide="trash-2" class="action-icon"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i data-lucide="users"></i>
                            </div>
                            <h3>No Authors Found</h3>
                            <p>Start by adding your first author using the form on the left.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const submitBtn = document.getElementById("submit-btn");

        // Form submission with loading state
        document.getElementById("addAuthorForm").addEventListener("submit", function(e) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="loading-spinner"></div> Adding Author to Library...';
        });

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        const authorCards = document.querySelectorAll('.author-card');
        
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            
            authorCards.forEach(card => {
                const authorName = card.querySelector('.author-name').textContent.toLowerCase();
                const authorBio = card.querySelector('.detail-value').textContent.toLowerCase();
                
                if (authorName.includes(searchTerm) || authorBio.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    function editAuthor(authorId) {
        // Redirect to edit page or show modal
        window.location.href = 'edit_author.php?id=' + authorId;
    }

    function deleteAuthor(authorId, authorName) {
        Swal.fire({
            title: 'Delete Author?',
            html: `<p>Are you sure you want to delete <strong>${authorName}</strong>?</p>
                   <p style="color: #ef4444; font-size: 14px; margin-top: 10px;">
                   <i data-lucide="alert-triangle"></i> This action cannot be undone and will affect all books by this author.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Delete Author',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request to delete author
                fetch('delete_author.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `author_id=${authorId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: `Author "${authorName}" has been deleted.`,
                            icon: 'success',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to delete author. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while deleting the author.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                });
            }
        });
    }
</script>

<?php
require_once 'footer.php';
?>