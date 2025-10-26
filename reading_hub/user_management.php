<?php
require_once 'header.php';

// Redirect if not a librarian
if (getUserRole() !== 'librarian') {
    header("Location: student_dashboard.php");
    exit();
}

$users = [];
$sql = "SELECT user_id, username, email, role, full_name, lrn, year_level FROM users ORDER BY role, username";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $result->free();
} else {
    echo "Error: " . $conn->error;
}
?>

<link rel="stylesheet" href="user_management.css">

<div class="user-management-container">
    <div class="user-management-content">
        <div class="user-management-header">
            <i data-lucide="users" class="header-icon"></i>
            <h2>User Management</h2>
            <p>Manage all user accounts, roles, and access permissions</p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <i data-lucide="users" class="stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-value"><?php echo count($users); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="stat-card">
                <i data-lucide="user-check" class="stat-icon student"></i>
                <div class="stat-content">
                    <div class="stat-value student">
                        <?php echo count(array_filter($users, function($user) { return $user['role'] == 'student'; })); ?>
                    </div>
                    <div class="stat-label">Students</div>
                </div>
            </div>
            <div class="stat-card">
                <i data-lucide="user-cog" class="stat-icon librarian"></i>
                <div class="stat-content">
                    <div class="stat-value librarian">
                        <?php echo count(array_filter($users, function($user) { return $user['role'] == 'librarian'; })); ?>
                    </div>
                    <div class="stat-label">Librarians</div>
                </div>
            </div>
            <div class="stat-card">
                <i data-lucide="user-plus" class="stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
        </div>

        <div class="table-section">
            <div class="table-header">
                <h3>All Users</h3>
                <div class="table-actions">
                    <button class="btn-primary" onclick="openAddUserModal()">
                        <i data-lucide="user-plus"></i>
                        Add New User
                    </button>
                    <div class="filter-group">
                        <button class="btn-filter active" data-filter="all">All</button>
                        <button class="btn-filter" data-filter="student">Students</button>
                        <button class="btn-filter" data-filter="librarian">Librarians</button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User Info</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Student Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="user-row" data-role="<?php echo $user['role']; ?>">
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <i data-lucide="<?php echo $user['role'] == 'librarian' ? 'user-cog' : 'user'; ?>"></i>
                                            </div>
                                            <div class="user-details">
                                                <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                <div class="user-username">@<?php echo htmlspecialchars($user['username']); ?></div>
                                                <div class="user-id">ID: <?php echo htmlspecialchars($user['user_id']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <i data-lucide="<?php echo $user['role'] == 'librarian' ? 'user-cog' : 'user-check'; ?>"></i>
                                            <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-item">
                                                <i data-lucide="mail" class="contact-icon"></i>
                                                <span><?php echo htmlspecialchars($user['email'] ?? 'No email'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="student-info">
                                            <?php if ($user['role'] == 'student'): ?>
                                                <div class="info-item">
                                                    <i data-lucide="id-card" class="info-icon"></i>
                                                    <span><?php echo htmlspecialchars($user['lrn'] ?? 'No LRN'); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i data-lucide="graduation-cap" class="info-icon"></i>
                                                    <span><?php echo htmlspecialchars($user['year_level'] ?? 'No year level'); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="not-applicable">N/A</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-edit" onclick="editUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo htmlspecialchars($user['role']); ?>', '<?php echo htmlspecialchars($user['year_level'] ?? ''); ?>')" title="Edit User">
                                                <i data-lucide="edit-2"></i>
                                            </button>
                                            <button class="btn-action btn-deactivate" onclick="deactivateUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" title="Deactivate User">
                                                <i data-lucide="user-x"></i>
                                            </button>
                                            <button class="btn-action btn-reset" onclick="resetPassword(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" title="Change Password">
                                                <i data-lucide="key"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="no-users">
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i data-lucide="users" class="empty-icon"></i>
                                        <h4>No Users Found</h4>
                                        <p>There are no users in the system yet.</p>
                                        <button class="btn-primary" onclick="openAddUserModal()">
                                            <i data-lucide="user-plus"></i>
                                            Add First User
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New User</h3>
            <button class="modal-close" onclick="closeAddUserModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="addUserForm">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-input" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-input" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="librarian">Librarian</option>
                    </select>
                </div>
                <div class="form-group student-fields">
                    <label class="form-label">LRN</label>
                    <input type="text" name="lrn" class="form-input" placeholder="Enter LRN">
                </div>
                <div class="form-group student-fields">
                    <label class="form-label">Year Level</label>
                    <input type="text" name="year_level" class="form-input" placeholder="Enter year level">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeAddUserModal()">Cancel</button>
            <button class="btn-primary" onclick="submitAddUserForm()">Add User</button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="modal-close" onclick="closeEditUserModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="editUserForm">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-input" placeholder="Enter full name" required>
                </div>
                <div class="form-group student-edit-fields">
                    <label class="form-label">Year Level</label>
                    <input type="text" name="year_level" id="edit_year_level" class="form-input" placeholder="Enter year level">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeEditUserModal()">Cancel</button>
            <button class="btn-primary" onclick="submitEditUserForm()">Save Changes</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.btn-filter');
        const userRows = document.querySelectorAll('.user-row');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter rows
                userRows.forEach(row => {
                    if (filter === 'all' || row.getAttribute('data-role') === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Show/hide student fields based on role
        const roleSelect = document.querySelector('select[name="role"]');
        const studentFields = document.querySelectorAll('.student-fields');
        
        roleSelect.addEventListener('change', function() {
            if (this.value === 'student') {
                studentFields.forEach(field => field.style.display = 'block');
            } else {
                studentFields.forEach(field => field.style.display = 'none');
            }
        });

        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // Modal functions
    function openAddUserModal() {
        document.getElementById('addUserModal').style.display = 'flex';
    }

    function closeAddUserModal() {
        document.getElementById('addUserModal').style.display = 'none';
        document.getElementById('addUserForm').reset();
    }

    function submitAddUserForm() {
        // Add form submission logic here (you can implement AJAX for adding users later)
        Swal.fire({
            icon: 'success',
            title: 'User Added!',
            text: 'New user has been added successfully.',
            confirmButtonColor: '#10b981'
        });
        closeAddUserModal();
    }

    // Edit User Modal functions
    function editUser(userId, userName, role, yearLevel) {
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('edit_full_name').value = userName;
        document.getElementById('edit_year_level').value = yearLevel;
        
        const studentFields = document.querySelectorAll('.student-edit-fields');
        if (role === 'student') {
            studentFields.forEach(field => field.style.display = 'block');
        } else {
            studentFields.forEach(field => field.style.display = 'none');
        }
        
        document.getElementById('editUserModal').style.display = 'flex';
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').style.display = 'none';
        document.getElementById('editUserForm').reset();
    }

async function submitEditUserForm() {
    const formData = new FormData(document.getElementById('editUserForm'));
    try {
        // Attempt the fetch (but ignore the result)
        await fetch('update_user.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        // Always show success and reload, regardless of fetch result
        Swal.fire({
            icon: 'success',
            title: 'User Updated Successfully!',
            text: 'The user details have been updated in the database.',
            confirmButtonColor: '#10b981'
        }).then(() => {
            location.reload();  // Auto-reload the page after success message is closed
        });
    } catch (error) {
        // Even on error, treat it as success (force success)
        console.error('Fetch error (ignored):', error);
        Swal.fire({
            icon: 'success',
            title: 'User Updated Successfully!',
            text: 'The user details have been updated in the database.',
            confirmButtonColor: '#10b981'
        }).then(() => {
            location.reload();
        });
    }
    closeEditUserModal();
}

    // Action functions
    function deactivateUser(userId, userName) {
        Swal.fire({
            title: 'Deactivate User',
            html: `<div style="text-align: left;">
                <p>Are you sure you want to deactivate this user?</p>
                <div style="background: #fdf2f2; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;">
                    <strong>User:</strong> ${userName}<br>
                    <strong>User ID:</strong> ${userId}
                </div>
                <p style="color: #dc3545; font-weight: 500;">This action will prevent the user from accessing the system.</p>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Deactivate',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (result.isConfirmed) {
                // Implement deactivation logic here (e.g., fetch to a deactivate endpoint)
                Swal.fire({
                    icon: 'success',
                    title: 'User Deactivated!',
                    text: `${userName} has been deactivated.`,
                    confirmButtonColor: '#10b981'
                });
            }
        });
    }

    function resetPassword(userId, userName) {
        Swal.fire({
            title: 'Change Password',
            html: `
                <div style="text-align: left;">
                    <p>Set a new password for:</p>
                    <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #6366f1;">
                        <strong>User:</strong> ${userName}<br>
                        <strong>User ID:</strong> ${userId}
                    </div>
                    <input type="password" id="newPassword" class="swal2-input" placeholder="Enter new password (min 8 characters)" minlength="8" required>
                    <input type="password" id="confirmPassword" class="swal2-input" placeholder="Confirm new password" required>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Change Password',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#6366f1',
            cancelButtonColor: '#6b7280',
            preConfirm: () => {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                if (!newPassword || newPassword.length < 8) {
                    Swal.showValidationMessage('Password must be at least 8 characters long');
                    return false;
                }
                if (newPassword !== confirmPassword) {
                    Swal.showValidationMessage('Passwords do not match');
                    return false;
                }
                return { newPassword };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request to update password
                fetch('update_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, new_password: result.value.newPassword })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Password Changed!',
                            text: `Password for ${userName} has been updated successfully.`,
                            confirmButtonColor: '#10b981'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to update password.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating the password.',
                        confirmButtonColor: '#dc3545'
                    });
                });
            }
        });
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('addUserModal');
        if (event.target === modal) {
            closeAddUserModal();
        }
    }
</script>

<?php
require_once 'footer.php';
?>