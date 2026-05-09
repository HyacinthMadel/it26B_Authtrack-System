<?php
/**
 * AuthTrack Monitoring — Register Page
 */

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: /pangit/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — AuthTrack Monitoring</title>
  <link rel="stylesheet" href="/pangit/public/css/style.css">
</head>
<body>

<div class="auth-wrap">
  <div class="auth-card">

    <div class="auth-logo">
      <div class="logo-icon">🔐</div>
      AuthTrack Monitoring
    </div>
    <p class="auth-subtitle">Create your account</p>

    <form id="registerForm" autocomplete="off">
      <div class="form-group">
        <label class="form-label" for="name">Full Name</label>
        <input class="form-input" type="text" id="name" name="name"
               placeholder="Jane Doe" required autofocus>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input class="form-input" type="email" id="email" name="email"
               placeholder="you@example.com" required>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input class="form-input" type="password" id="password" name="password"
                 placeholder="Min 6 chars" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="confirm">Confirm</label>
          <input class="form-input" type="password" id="confirm" name="confirm"
                 placeholder="Repeat password" required>
        </div>
      </div>

      <div class="alert alert-error"   id="errorBox"   role="alert"><span>⚠</span><span id="errorMsg"></span></div>
      <div class="alert alert-success" id="successBox" role="alert"><span>✓</span><span id="successMsg"></span></div>

      <button class="btn btn-primary mt-2" type="submit" id="submitBtn">
        Create Account
      </button>
    </form>

    <div class="auth-divider">or</div>

    <p class="text-center text-sm text-muted">
      Already have an account? <a href="/pangit/login.php">Sign in →</a>
    </p>

  </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const btn        = document.getElementById('submitBtn');
  const errorBox   = document.getElementById('errorBox');
  const errorMsg   = document.getElementById('errorMsg');
  const successBox = document.getElementById('successBox');
  const successMsg = document.getElementById('successMsg');

  const pw  = document.getElementById('password').value;
  const con = document.getElementById('confirm').value;
  if (pw !== con) {
    errorMsg.textContent = 'Passwords do not match.';
    errorBox.className = 'alert alert-error show';
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Creating account…';
  errorBox.classList.remove('show');
  successBox.classList.remove('show');

  const formData = new FormData(e.target);

  try {
    const res  = await fetch('/pangit/api/register.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
      successMsg.textContent = data.message;
      successBox.className = 'alert alert-success show';
      e.target.reset();
      setTimeout(() => window.location.href = '/pangit/login.php', 2000);
    } else {
      errorMsg.textContent = data.message;
      errorBox.className = 'alert alert-error show';
    }
  } catch (err) {
    errorMsg.textContent = 'Network error. Please try again.';
    errorBox.className = 'alert alert-error show';
  }

  btn.disabled = false;
  btn.textContent = 'Create Account';
});
</script>

</body>
</html>
