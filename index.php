<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page">
  <header>
    <a href="index.php">TITLE</a>
    <a href="home.php">HOME</a>
    <a href="insert.php">INSERT</a>
    <a href="profile.php">PROFILE</a>
    <a href="logout.php">LOG OUT</a>
  </header>

  <main>
    <div class="content">
      <h1>TABLE OF CONTENTS</h1>
      <p style="margin-top:20px; font-size:18px;">Welcome, <?php echo htmlspecialchars(get_user_name()); ?>!</p>
      <ul style="margin-top:30px; list-style:none; font-size:20px; line-height:2.2;">
      </ul>
    </div>

    <aside class="sidebar">
      <h2>MENU</h2>
      <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="insert.php">Insert</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="profile.php">Profile</a></li>
      </ul>
    </aside>
  </main>

  <footer>
    <a href="about.php">@ABOUT US</a>
    <a href="contact.php">@CONTACT</a>
    <a href="privacy.php">@PRIVACY</a>
  </footer>

</div>
</body>
</html>
