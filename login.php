<?php
/**
 * AuthTrack Monitoring — Login Page
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
  <title>Login — AuthTrack Monitoring</title>
  <link rel="stylesheet" href="/pangit/public/css/style.css">
</head>
<body>

<div class="auth-wrap">
  <div class="auth-card">

    <div class="auth-logo">
      <div class="logo-icon">🔐</div>
      AuthTrack Monitoring
    </div>
    <p class="auth-subtitle">Smart Login &amp; Activity Monitoring</p>

    <form id="loginForm" autocomplete="off">
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input class="form-input" type="email" id="email" name="email"
               placeholder="you@example.com" required autofocus>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="form-input" type="password" id="password" name="password"
               placeholder="••••••••" required>
      </div>

      <div class="alert alert-error" id="alertBox" role="alert">
        <span>⚠</span>
        <span id="alertMsg"></span>
      </div>

      <button class="btn btn-primary mt-2" type="submit" id="submitBtn">
        Sign In
      </button>
    </form>

    <div class="auth-divider">or</div>

    <p class="text-center text-sm text-muted">
      Don't have an account?
      <a href="/pangit/register.php">Create one →</a>
    </p>

    <div style="margin-top:1.5rem;padding:1rem;background:var(--bg3);border-radius:var(--radius);font-size:0.78rem;color:var(--text2);border:1px solid var(--border);">
      <strong style="color:var(--text);">Sample Accounts</strong>
      <span style="color:var(--text3);"> (run setup.php first!)</span><br><br>
      admin@authtrack.com &nbsp;→ <code style="color:var(--accent);">Admin@123</code><br>
      editor@authtrack.com → <code style="color:var(--accent);">Editor@123</code><br>
      viewer@authtrack.com → <code style="color:var(--accent);">Viewer@123</code>
    </div>

  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const btn      = document.getElementById('submitBtn');
  const alertEl  = document.getElementById('alertBox');
  const alertMsg = document.getElementById('alertMsg');

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Signing in…';
  alertEl.classList.remove('show');

  const formData = new FormData(e.target);
  formData.append('action', 'login');

  try {
    const res  = await fetch('/pangit/api/auth.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
      btn.innerHTML = '✓ Redirecting…';
      window.location.href = '/pangit/dashboard.php';
    } else {
      alertMsg.textContent = data.message;
      alertEl.className = 'alert alert-error show';
      btn.disabled = false;
      btn.textContent = 'Sign In';
    }
  } catch (err) {
    alertMsg.textContent = 'Network error. Please try again.';
    alertEl.className = 'alert alert-error show';
    btn.disabled = false;
    btn.textContent = 'Sign In';
  }
});
</script>

</body>
</html>
