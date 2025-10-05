<?php
require_once 'functions.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

$username = $password = $role = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate role
    if (empty(trim($_POST["role"]))) {
        $login_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err) && empty($login_err)) {
        $sql = "SELECT user_id, username, password_hash, role, full_name FROM users WHERE username = ? AND role = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $param_username, $param_role);
            $param_username = $username;
            $param_role = $role;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($user_id, $username, $hashed_password, $user_role, $full_name);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $user_id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $user_role;
                            $_SESSION["full_name"] = $full_name; // Store full name

                            logAudit($user_id, 'login', $user_id, 'User logged in successfully.');

                            redirectToDashboard();
                        } else {
                            $login_err = "Invalid username, password, or role.";
                        }
                    }
                } else {
                    $login_err = "Invalid username, password, or role.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
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
    <title>Login - BookHive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
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

        <!-- Login Form -->
        <div class="flex items-center justify-center px-4 py-20">
            <div class="auth-container card">
                <div class="space-y-1 text-center pb-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-[var(--primary)] to-[var(--secondary)] rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                        <i data-lucide="book-open" class="w-8 h-8 text-primary-foreground"></i>
                    </div>
                    <h2 class="text-3xl text-foreground">Welcome Back</h2>
                    <p class="text-muted-foreground">
                        Sign in to access your BookHive account
                    </p>
                </div>

                <?php
                if (!empty($login_err)) {
                    echo '<div class="alert alert-danger">' . $login_err . '</div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4">
                    <div class="form-group">
                        <label for="role">I am a:</label>
                        <select name="role" id="role" class="form-control">
                            <option value="">Select role</option>
                            <option value="student" <?php echo ($role == 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="librarian" <?php echo ($role == 'librarian') ? 'selected' : ''; ?>>Librarian</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Enter your username">
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Enter your password">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-primary w-full py-6 rounded-xl shadow-lg" value="Sign In as <?php echo ($role == 'student' ? 'Student' : ($role == 'librarian' ? 'Librarian' : 'User')); ?>">
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <a href="#" class="btn btn-link">Forgot Password?</a>
                        <a href="signup.php" class="btn btn-link">Create Account</a>
                    </div>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-6 p-4 bg-[var(--muted)] rounded-xl border border-[var(--accent)]/20">
                    <p class="text-sm font-medium mb-2 text-foreground">Demo Credentials:</p>
                    <div class="text-xs space-y-1 text-muted-foreground">
                        <p><strong class="text-[var(--secondary)]">Student:</strong> studentuser / password</p>
                        <p><strong class="text-[var(--primary)]">Librarian:</strong> librarianuser / password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>