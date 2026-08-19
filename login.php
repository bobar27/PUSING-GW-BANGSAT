<?php
require_once __DIR__ . '/config/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'signup') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if (!$name || !$email || !$password) {
            $error = 'All fields are required.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'An account with that email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $name, $email, $hash);

                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $conn->insert_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Sign up failed. Please try again.';
                }
            }
            $stmt->close();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $error = 'Email and password are required.';
        } else {
            $stmt = $conn->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign In / Sign Up</title>
<link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page">
  <header>
    <a href="index.php">TITLE</a>
    <a href="index.php">HOME</a>
    <a href="insert.php">INSERT</a>
    <a href="login.php">SIGN IN/LOG IN</a>
  </header>

  <main class="auth-main">
    <div class="auth-box">
      <h1 id="formTitle">SIGN IN</h1>

      <div class="auth-tabs">
        <button type="button" id="tabLogin" class="active" onclick="showForm('login')">LOG IN</button>
        <button type="button" id="tabSignup" onclick="showForm('signup')">SIGN UP</button>
      </div>

      <?php if ($error): ?>
      <div class="auth-error" style="display:block;"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <!-- Login form -->
      <form id="loginForm" class="auth-form active" method="POST" action="">
        <input type="hidden" name="action" value="login">
        <div class="auth-field">
          <label for="loginEmail">EMAIL</label>
          <input type="email" id="loginEmail" name="email" placeholder="you@example.com" required>
        </div>
        <div class="auth-field">
          <label for="loginPassword">PASSWORD</label>
          <input type="password" id="loginPassword" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="auth-submit-btn">LOG IN</button>
        <div class="auth-switch-text">
          DON'T HAVE AN ACCOUNT?
          <button type="button" onclick="showForm('signup')">SIGN UP</button>
        </div>
      </form>

      <!-- Signup form -->
      <form id="signupForm" class="auth-form" method="POST" action="">
        <input type="hidden" name="action" value="signup">
        <div class="auth-field">
          <label for="signupName">FULL NAME</label>
          <input type="text" id="signupName" name="name" placeholder="Your Name" required>
        </div>
        <div class="auth-field">
          <label for="signupEmail">EMAIL</label>
          <input type="email" id="signupEmail" name="email" placeholder="you@example.com" required>
        </div>
        <div class="auth-field">
          <label for="signupPassword">PASSWORD</label>
          <input type="password" id="signupPassword" name="password" placeholder="•••••••• (min 8 characters)" required minlength="8">
        </div>
        <div class="auth-field">
          <label for="signupConfirm">CONFIRM PASSWORD</label>
          <input type="password" id="signupConfirm" name="confirm" placeholder="••••••••" required minlength="8">
        </div>
        <button type="submit" class="auth-submit-btn">SIGN UP</button>
        <div class="auth-switch-text">
          ALREADY HAVE AN ACCOUNT?
          <button type="button" onclick="showForm('login')">LOG IN</button>
        </div>
      </form>

    </div>
  </main>

  <footer>
    <a href="about.php">@ABOUT US</a>
    <a href="contact.php">@CONTACT</a>
    <a href="privacy.php">@PRIVACY</a>
  </footer>

</div>

<script>
  function showForm(type) {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const tabLogin = document.getElementById('tabLogin');
    const tabSignup = document.getElementById('tabSignup');
    const title = document.getElementById('formTitle');

    if (type === 'login') {
      loginForm.classList.add('active');
      signupForm.classList.remove('active');
      tabLogin.classList.add('active');
      tabSignup.classList.remove('active');
      title.textContent = 'SIGN IN';
    } else {
      signupForm.classList.add('active');
      loginForm.classList.remove('active');
      tabSignup.classList.add('active');
      tabLogin.classList.remove('active');
      title.textContent = 'SIGN UP';
    }
  }
</script>

</body>
</html>
