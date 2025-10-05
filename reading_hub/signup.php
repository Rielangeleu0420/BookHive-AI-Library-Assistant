<?php
require_once 'functions.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

$username = $email = $lrn = $full_name = $year_level = $password = $confirm_password = "";
$username_err = $email_err = $lrn_err = $full_name_err = $year_level_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // Prepare a select statement
        $sql = "SELECT user_id FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already registered.";
                } else {
                    $email = trim($_POST["email"]);
                }
            }
            $stmt->close();
        }
    }

    // Determine role
    $role = isset($_POST["role"]) && $_POST["role"] === 'librarian' ? 'librarian' : 'student';

    // Validate LRN (for students only)
    if ($role === 'student') {
        if (empty(trim($_POST["lrn"]))) {
            $lrn_err = "Please enter your LRN.";
        } elseif (!preg_match('/^\d{12}$/', trim($_POST["lrn"]))) {
            $lrn_err = "LRN must be 12 digits.";
        } else {
            $sql = "SELECT user_id FROM users WHERE lrn = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $param_lrn);
                $param_lrn = trim($_POST["lrn"]);
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows == 1) {
                        $lrn_err = "This LRN is already registered.";
                    } else {
                        $lrn = trim($_POST["lrn"]);
                    }
                }
                $stmt->close();
            }
        }
    }

    // Validate Full Name
    if (empty(trim($_POST["full_name"]))) {
        $full_name_err = "Please enter your full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }

    // Validate Year Level (for students only)
    if ($role === 'student') {
        if (empty(trim($_POST["year_level"]))) {
            $year_level_err = "Please enter your year level.";
        } else {
            $year_level = trim($_POST["year_level"]);
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($email_err) && empty($lrn_err) && empty($full_name_err) && empty($year_level_err) && empty($password_err) && empty($confirm_password_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password_hash, email, role, lrn, full_name, year_level) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssssss", $param_username, $param_password, $param_email, $param_role, $param_lrn, $param_full_name, $param_year_level);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_email = $email;
            $param_role = $role;
            $param_lrn = ($role === 'student') ? $lrn : NULL;
            $param_full_name = $full_name;
            $param_year_level = ($role === 'student') ? $year_level : NULL;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page
                logAudit($conn->insert_id, 'signup', $conn->insert_id, 'New user signed up.');
                header("location: login.php");
            } else {
                echo "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - BookHive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const studentFields = document.getElementById('student-fields');

            function toggleStudentFields() {
                if (roleSelect.value === 'student') {
                    studentFields.style.display = 'block';
                } else {
                    studentFields.style.display = 'none';
                }
            }

            roleSelect.addEventListener('change', toggleStudentFields);
            toggleStudentFields(); // Call on load to set initial state
        });
    </script>
</head>
<body>
    <div class="min-h-screen bg-gradient-to-br from-[var(--background)] via-[var(--muted)] to-[var(--accent)]">
        <!-- Header -->
        <header class="header-primary backdrop-blur-sm border-b border-border px-6 py-4">
            <div class="flex items-center justify-between max-w-7xl mx-auto">
                <a href="index.php" class="flex items-center space-x-2 text-primary-foreground hover:bg-primary/20 btn">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back to Home</span>
                </a>
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-[var(--secondary)] rounded-xl flex items-center justify-center shadow-lg">
                        <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-semibold text-primary-foreground">BookHive</span>
                </div>
            </div>
        </header>

        <div class="flex items-center justify-center px-4 py-20">
            <div class="auth-container card">
                <div class="space-y-1 text-center pb-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-[var(--primary)] to-[var(--secondary)] rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                        <i data-lucide="book-open" class="w-8 h-8 text-primary-foreground"></i>
                    </div>
                    <h2 class="text-3xl text-foreground">Sign Up for BookHive</h2>
                    <p class="text-muted-foreground">
                        Please fill this form to create an account.
                    </p>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4">
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="role" class="form-control">
                            <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="librarian" <?php echo (isset($_POST['role']) && $_POST['role'] == 'librarian') ? 'selected' : ''; ?>>Librarian</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                    <div class="form-group" id="student-fields">
                        <label>LRN (Learning Reference Number)</label>
                        <input type="text" name="lrn" class="form-control <?php echo (!empty($lrn_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $lrn; ?>" maxlength="12">
                        <span class="invalid-feedback"><?php echo $lrn_err; ?></span>
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $full_name; ?>">
                        <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                        <label>Year Level</label>
                        <input type="text" name="year_level" class="form-control <?php echo (!empty($year_level_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $year_level; ?>">
                        <span class="invalid-feedback"><?php echo $year_level_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary w-full py-6 rounded-xl shadow-lg" value="Sign Up">
                    </div>
                    <p>Already have an account? <a href="login.php">Login here</a>.</p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>