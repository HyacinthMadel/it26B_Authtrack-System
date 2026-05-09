<?php
/**
 * AuthTrack Monitoring — Dashboard (Frontend)
 * dashboard.php
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$userName  = h($_SESSION['name']  ?? 'User');
$userEmail = h($_SESSION['email'] ?? '');
$userRole  = h($_SESSION['role']  ?? 'viewer');
$initials  = strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — AuthTrack Monitoring</title>
  <link rel="stylesheet" href="/pangit/public/css/style.css">
</head>
<body>

<div id="toast-container"></div>

<div class="app">

  <!-- ── Sidebar ────────────────────────────────────────────── -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">🔐</div>
      AuthTrack
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a class="nav-item active" data-page="dashboard" href="#">
        <span class="nav-icon">📊</span> Dashboard
      </a>
      <a class="nav-item" data-page="users" href="#">
        <span class="nav-icon">👥</span> Users
      </a>
      <a class="nav-item" data-page="logs" href="#">
        <span class="nav-icon">📋</span> Activity Logs
      </a>
      <a class="nav-item" data-page="joins" href="#">
        <span class="nav-icon">🔗</span> SQL Joins Demo
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-avatar"><?= $initials ?></div>
        <div class="sidebar-user-info">
          <div class="sidebar-user-name"><?= $userName ?></div>
          <div class="sidebar-user-role"><?= $userRole ?></div>
        </div>
      </div>
      <button class="btn btn-danger btn-sm w-full" id="logoutBtn">
        Sign Out
      </button>
    </div>
  </aside>

  <!-- ── Main Content ───────────────────────────────────────── -->
  <div class="main">

    <!-- Topbar -->
    <header class="topbar">
      <div class="flex flex-center gap-2">
        <button class="btn btn-secondary btn-icon" id="sidebarToggle" style="display:none">☰</button>
        <span class="topbar-title" id="topbarTitle">Dashboard</span>
      </div>
      <div class="topbar-actions">
        <span class="badge badge-<?= $userRole ?>"><?= $userRole ?></span>
        <span class="text-sm text-muted"><?= $userEmail ?></span>
      </div>
    </header>

    <main class="content">

      <!-- ════════════════════════════════════════════
           PAGE: DASHBOARD
      ════════════════════════════════════════════ -->
      <div id="page-dashboard">

        <div class="stats-grid" id="statsGrid">
          <!-- Loaded via JS -->
          <div class="stat-card"><div class="skeleton" style="height:80px"></div></div>
          <div class="stat-card"><div class="skeleton" style="height:80px"></div></div>
          <div class="stat-card"><div class="skeleton" style="height:80px"></div></div>
          <div class="stat-card"><div class="skeleton" style="height:80px"></div></div>
        </div>

        <div class="dash-grid">
          <!-- Recent logs -->
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Recent Activity</div>
                <div class="card-subtitle">Last 10 events</div>
              </div>
              <a href="#" class="btn btn-secondary btn-sm" data-page="logs">View All</a>
            </div>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>User</th><th>Action</th><th>Details</th><th>IP</th><th>Time</th>
                  </tr>
                </thead>
                <tbody id="recentLogsBody">
                  <tr><td colspan="5"><div class="skeleton" style="height:40px;margin:.5rem 0"></div></td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Role breakdown -->
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Users by Role</div>
                <div class="card-subtitle">Role distribution</div>
              </div>
            </div>
            <div class="bar-chart" id="roleChart"></div>
          </div>

          <!-- Action breakdown -->
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Top Actions</div>
                <div class="card-subtitle">By log count</div>
              </div>
            </div>
            <div class="bar-chart" id="actionChart"></div>
          </div>
        </div>

      </div><!-- /page-dashboard -->


      <!-- ════════════════════════════════════════════
           PAGE: USERS
      ════════════════════════════════════════════ -->
      <div id="page-users" class="hidden">

        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title">Users</div>
              <div class="card-subtitle">Manage system users</div>
            </div>
            <?php if ($userRole === 'admin'): ?>
            <button class="btn btn-primary btn-sm" id="addUserBtn">+ Add User</button>
            <?php endif; ?>
          </div>

          <div class="toolbar">
            <div class="search-wrap">
              <span class="search-icon">🔍</span>
              <input class="form-input" type="text" id="userSearch" placeholder="Search name or email…">
            </div>
          </div>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th>
                </tr>
              </thead>
              <tbody id="usersBody">
                <tr><td colspan="6"><div class="skeleton" style="height:40px;margin:.5rem 0"></div></td></tr>
              </tbody>
            </table>
          </div>

          <div class="pagination" id="usersPagination"></div>
        </div>

      </div><!-- /page-users -->


      <!-- ════════════════════════════════════════════
           PAGE: LOGS
      ════════════════════════════════════════════ -->
      <div id="page-logs" class="hidden">

        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title">Activity Logs</div>
              <div class="card-subtitle">All system events</div>
            </div>
          </div>

          <div class="toolbar">
            <div class="search-wrap">
              <span class="search-icon">🔍</span>
              <input class="form-input" type="text" id="logSearch" placeholder="Search user or details…">
            </div>
            <select class="form-select" id="logFilter" style="width:160px">
              <option value="">All actions</option>
            </select>
          </div>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th><th>User</th><th>Role</th><th>Action</th><th>Details</th><th>IP</th><th>Time</th>
                </tr>
              </thead>
              <tbody id="logsBody">
                <tr><td colspan="7"><div class="skeleton" style="height:40px;margin:.5rem 0"></div></td></tr>
              </tbody>
            </table>
          </div>

          <div class="pagination" id="logsPagination"></div>
        </div>

      </div><!-- /page-logs -->


      <!-- ════════════════════════════════════════════
           PAGE: SQL JOINS DEMO
      ════════════════════════════════════════════ -->
      <div id="page-joins" class="hidden">

        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title">SQL Joins Demo</div>
              <div class="card-subtitle">Live examples using real data</div>
            </div>
          </div>

          <div class="tabs">
            <button class="tab-btn active" data-join="inner">INNER JOIN</button>
            <button class="tab-btn" data-join="left">LEFT JOIN</button>
            <button class="tab-btn" data-join="right">RIGHT JOIN</button>
            <button class="tab-btn" data-join="full">FULL OUTER JOIN</button>
          </div>

          <div class="alert alert-info show" id="joinDesc">
            <span>ℹ</span><span id="joinDescText">Loading…</span>
          </div>

          <div class="sql-block" id="joinSql">Loading SQL…</div>

          <div class="table-wrap">
            <table>
              <thead><tr id="joinHead"></tr></thead>
              <tbody id="joinBody">
                <tr><td><div class="skeleton" style="height:35px;margin:.4rem 0"></div></td></tr>
              </tbody>
            </table>
          </div>

          <div style="margin-top:.75rem;font-size:.78rem;color:var(--text3)">
            <span id="joinCount"></span> row(s) returned
          </div>
        </div>

      </div><!-- /page-joins -->

    </main>
  </div>
</div>

<!-- ── User Modal ──────────────────────────────────────────── -->
<div class="modal-overlay" id="userModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modalTitle">Add User</div>
      <button class="modal-close" id="modalClose">✕</button>
    </div>

    <div class="alert alert-error hidden" id="modalAlert">
      <span>⚠</span><span id="modalAlertMsg"></span>
    </div>

    <div class="form-group">
      <label class="form-label">Full Name</label>
      <input class="form-input" type="text" id="mName" placeholder="Jane Doe">
    </div>
    <div class="form-group">
      <label class="form-label">Email Address</label>
      <input class="form-input" type="email" id="mEmail" placeholder="jane@example.com">
    </div>
    <div class="form-group">
      <label class="form-label">Password <span style="color:var(--text3);font-weight:400">(leave blank to keep current)</span></label>
      <input class="form-input" type="password" id="mPassword" placeholder="Min 6 characters">
    </div>
    <div class="form-group" id="mRoleGroup">
      <label class="form-label">Role</label>
      <select class="form-select" id="mRole">
        <option value="1">Admin</option>
        <option value="2">Editor</option>
        <option value="3" selected>Viewer</option>
      </select>
    </div>

    <div class="modal-footer">
      <button class="btn btn-secondary" id="modalCancelBtn">Cancel</button>
      <button class="btn btn-primary" id="modalSaveBtn" style="width:140px">Save User</button>
    </div>
  </div>
</div>

<!-- ── Confirm Delete Modal ────────────────────────────────── -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal" style="max-width:380px">
    <div class="modal-header">
      <div class="modal-title">Delete User</div>
      <button class="modal-close" id="deleteModalClose">✕</button>
    </div>
    <p style="color:var(--text2);font-size:.87rem">Are you sure you want to delete <strong id="deleteUserName" style="color:var(--text)"></strong>? This action cannot be undone.</p>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="deleteCancelBtn">Cancel</button>
      <button class="btn btn-danger" id="deleteConfirmBtn">Delete User</button>
    </div>
  </div>
</div>

<script>
// ── Utilities ────────────────────────────────────────────────
const $ = id => document.getElementById(id);
const PAGE_ROLE = '<?= $userRole ?>';
const SESSION_ID = <?= $_SESSION['user_id'] ?? 0 ?>;

function toast(msg, type = 'success') {
  const el = document.createElement('div');
  el.className = `toast toast-${type}`;
  el.innerHTML = `<span>${type === 'success' ? '✓' : '✕'}</span> ${msg}`;
  $('toast-container').appendChild(el);
  setTimeout(() => el.remove(), 3500);
}

function fmt(dateStr) {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' }) +
         ' ' + d.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit' });
}

function badge(action) {
  const cls = {
    login:'badge-login', logout:'badge-logout', register:'badge-register',
    create_user:'badge-create_user', update_user:'badge-update_user', delete_user:'badge-delete_user'
  }[action] || 'badge-unknown';
  return `<span class="badge ${cls}">${action}</span>`;
}

function roleBadge(role) {
  return `<span class="badge badge-${role || 'unknown'}">${role || 'unknown'}</span>`;
}

// ── Navigation ───────────────────────────────────────────────
let currentPage = 'dashboard';

function navigate(page) {
  document.querySelectorAll('[id^="page-"]').forEach(el => el.classList.add('hidden'));
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));

  $(`page-${page}`).classList.remove('hidden');
  document.querySelector(`.nav-item[data-page="${page}"]`)?.classList.add('active');

  const titles = { dashboard:'Dashboard', users:'Users', logs:'Activity Logs', joins:'SQL Joins Demo' };
  $('topbarTitle').textContent = titles[page] || page;
  currentPage = page;

  if (page === 'dashboard') loadDashboard();
  if (page === 'users')     { usersPage = 1; loadUsers(); }
  if (page === 'logs')      { logsPage  = 1; loadLogs();  }
  if (page === 'joins')     loadJoin('inner');
}

document.querySelectorAll('.nav-item, [data-page]').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();
    const pg = el.dataset.page;
    if (pg) navigate(pg);
  });
});

// ── Logout ────────────────────────────────────────────────────
$('logoutBtn').addEventListener('click', async () => {
  const fd = new FormData(); fd.append('action', 'logout');
  await fetch('/pangit/api/auth.php', { method:'POST', body:fd });
  window.location.href = '/pangit/login.php';
});

// ── Dashboard ────────────────────────────────────────────────
async function loadDashboard() {
  const res  = await fetch('/pangit/api/dashboard.php');
  const data = await res.json();
  if (!data.success) return;

  // Stats
  $('statsGrid').innerHTML = `
    <div class="stat-card">
      <div class="stat-icon blue">👥</div>
      <div class="stat-value">${data.totalUsers}</div>
      <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon cyan">📋</div>
      <div class="stat-value">${data.totalLogs}</div>
      <div class="stat-label">Activity Logs</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green">🕐</div>
      <div class="stat-value">${data.activityByDay.length > 0 ? data.activityByDay[data.activityByDay.length-1].count : 0}</div>
      <div class="stat-label">Events Today</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon amber">🆕</div>
      <div class="stat-value" style="font-size:1rem;margin-top:.4rem">${data.newestUser ? data.newestUser.name : '—'}</div>
      <div class="stat-label">Newest User</div>
    </div>
  `;

  // Recent logs
  $('recentLogsBody').innerHTML = data.recentLogs.map(l => `
    <tr>
      <td>
        <div class="td-main">${l.user_name}</div>
        <div style="font-size:.72rem;color:var(--text3)">${l.user_email}</div>
      </td>
      <td>${badge(l.action)}</td>
      <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${l.details || '—'}</td>
      <td><code style="font-size:.75rem">${l.ip_address}</code></td>
      <td style="font-size:.75rem;white-space:nowrap">${fmt(l.created_at)}</td>
    </tr>
  `).join('') || '<tr><td colspan="5" class="empty-state">No logs yet</td></tr>';

  // Role chart
  const maxRole = Math.max(...data.roleBreakdown.map(r => r.count), 1);
  $('roleChart').innerHTML = data.roleBreakdown.map(r => `
    <div class="bar-row">
      <div class="bar-label">${r.role}</div>
      <div class="bar-track"><div class="bar-fill" style="width:${(r.count/maxRole*100)}%;background:var(--primary)"></div></div>
      <div class="bar-count">${r.count}</div>
    </div>
  `).join('');

  // Action chart
  const maxAct = Math.max(...data.actionBreakdown.map(a => a.count), 1);
  const colors = ['var(--primary)','var(--accent)','var(--success)','var(--warning)','var(--danger)'];
  $('actionChart').innerHTML = data.actionBreakdown.map((a,i) => `
    <div class="bar-row">
      <div class="bar-label">${a.action}</div>
      <div class="bar-track"><div class="bar-fill" style="width:${(a.count/maxAct*100)}%;background:${colors[i%colors.length]}"></div></div>
      <div class="bar-count">${a.count}</div>
    </div>
  `).join('');
}

// ── Users ─────────────────────────────────────────────────────
let usersPage = 1;
let userSearch = '';

async function loadUsers() {
  const params = new URLSearchParams({ action:'list', page:usersPage, search:userSearch });
  const res  = await fetch(`/pangit/api/users.php?${params}`);
  const data = await res.json();
  if (!data.success) return;

  $('usersBody').innerHTML = data.data.length ? data.data.map(u => `
    <tr>
      <td style="color:var(--text3)">#${u.id}</td>
      <td><div class="td-main">${u.name}</div></td>
      <td style="color:var(--text2)">${u.email}</td>
      <td>${roleBadge(u.role)}</td>
      <td style="font-size:.75rem;color:var(--text3)">${fmt(u.created_at)}</td>
      <td>
        <div class="flex gap-2">
          ${PAGE_ROLE !== 'viewer' ? `<button class="btn btn-secondary btn-sm" onclick="openEditUser(${u.id})">Edit</button>` : ''}
          ${PAGE_ROLE === 'admin' && u.id !== SESSION_ID ? `<button class="btn btn-danger btn-sm" onclick="confirmDelete(${u.id},'${u.name.replace(/'/g,"\\'")}')">Delete</button>` : ''}
        </div>
      </td>
    </tr>
  `).join('') : '<tr><td colspan="6" class="empty-state"><div class="empty-icon">👥</div>No users found</td></tr>';

  renderPagination('usersPagination', data.page, data.pages, p => { usersPage = p; loadUsers(); });
}

$('userSearch').addEventListener('input', () => {
  userSearch = $('userSearch').value;
  usersPage = 1;
  loadUsers();
});

// ── Logs ──────────────────────────────────────────────────────
let logsPage = 1;
let logSearch = '', logFilter = '';

async function loadLogs() {
  const params = new URLSearchParams({ page:logsPage, search:logSearch, filter:logFilter });
  const res  = await fetch(`/pangit/api/logs.php?${params}`);
  const data = await res.json();
  if (!data.success) return;

  // Populate filter
  if ($('logFilter').options.length === 1) {
    data.actions.forEach(a => {
      const opt = document.createElement('option');
      opt.value = a; opt.textContent = a;
      $('logFilter').appendChild(opt);
    });
  }

  $('logsBody').innerHTML = data.data.length ? data.data.map(l => `
    <tr>
      <td style="color:var(--text3)">#${l.id}</td>
      <td>
        <div class="td-main">${l.user_name || 'Deleted'}</div>
        <div style="font-size:.72rem;color:var(--text3)">${l.user_email || '—'}</div>
      </td>
      <td>${roleBadge(l.user_role)}</td>
      <td>${badge(l.action)}</td>
      <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text2)">${l.details || '—'}</td>
      <td><code style="font-size:.75rem">${l.ip_address}</code></td>
      <td style="font-size:.75rem;white-space:nowrap">${fmt(l.created_at)}</td>
    </tr>
  `).join('') : '<tr><td colspan="7" class="empty-state"><div class="empty-icon">📋</div>No logs found</td></tr>';

  renderPagination('logsPagination', data.page, data.pages, p => { logsPage = p; loadLogs(); });
}

$('logSearch').addEventListener('input', () => { logSearch=$('logSearch').value; logsPage=1; loadLogs(); });
$('logFilter').addEventListener('change', () => { logFilter=$('logFilter').value; logsPage=1; loadLogs(); });

// ── SQL Joins ─────────────────────────────────────────────────
let activeJoin = 'inner';

document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    loadJoin(btn.dataset.join);
  });
});

async function loadJoin(type) {
  activeJoin = type;
  $('joinSql').textContent = 'Loading…';
  $('joinBody').innerHTML  = '<tr><td><div class="skeleton" style="height:35px"></div></td></tr>';
  $('joinCount').textContent = '';

  const res  = await fetch(`/pangit/api/joins.php?type=${type}`);
  const data = await res.json();
  if (!data.success) { toast(data.message, 'error'); return; }

  $('joinDescText').textContent = data.description;
  $('joinSql').textContent      = data.sql;
  $('joinCount').textContent    = data.count;

  if (data.data.length === 0) {
    $('joinHead').innerHTML = '';
    $('joinBody').innerHTML = '<tr><td class="empty-state" colspan="10"><div class="empty-icon">🔗</div>No rows returned</td></tr>';
    return;
  }

  const cols = Object.keys(data.data[0]);
  $('joinHead').innerHTML = cols.map(c => `<th>${c}</th>`).join('');
  $('joinBody').innerHTML = data.data.map(row =>
    '<tr>' + cols.map(c => `<td>${row[c] ?? '<span style="color:var(--text3)">NULL</span>'}</td>`).join('') + '</tr>'
  ).join('');
}

// ── Pagination ───────────────────────────────────────────────
function renderPagination(containerId, page, pages, onChange) {
  const el = $(containerId);
  if (pages <= 1) { el.innerHTML = ''; return; }
  let html = `<button class="page-btn" ${page===1?'disabled':''} onclick="(${onChange})(${page-1})">←</button>`;
  for (let i=1; i<=pages; i++) {
    if (i===1||i===pages||Math.abs(i-page)<=1)
      html += `<button class="page-btn ${i===page?'active':''}" onclick="(${onChange})(${i})">${i}</button>`;
    else if (Math.abs(i-page)===2)
      html += `<span style="color:var(--text3);padding:0 .2rem">…</span>`;
  }
  html += `<button class="page-btn" ${page===pages?'disabled':''} onclick="(${onChange})(${page+1})">→</button>`;
  el.innerHTML = html;
}

// ── User Modal ───────────────────────────────────────────────
let editUserId = null;

$('addUserBtn')?.addEventListener('click', () => {
  editUserId = null;
  $('modalTitle').textContent = 'Add User';
  $('mName').value = ''; $('mEmail').value = ''; $('mPassword').value = '';
  $('mRole').value = '3';
  $('modalAlert').classList.add('hidden');
  $('mRoleGroup').style.display = PAGE_ROLE==='admin' ? '' : 'none';
  $('userModal').classList.add('show');
});

async function openEditUser(id) {
  editUserId = id;
  $('modalTitle').textContent = 'Edit User';
  $('mPassword').value = '';
  $('modalAlert').classList.add('hidden');
  $('mRoleGroup').style.display = PAGE_ROLE==='admin' ? '' : 'none';

  const res  = await fetch(`/pangit/api/users.php?action=get&id=${id}`);
  const data = await res.json();
  if (!data.success) { toast('Failed to load user', 'error'); return; }

  $('mName').value  = data.data.name;
  $('mEmail').value = data.data.email;
  $('mRole').value  = data.data.role_id;
  $('userModal').classList.add('show');
}

function closeUserModal() { $('userModal').classList.remove('show'); }
$('modalClose').addEventListener('click', closeUserModal);
$('modalCancelBtn').addEventListener('click', closeUserModal);
$('userModal').addEventListener('click', e => { if (e.target === $('userModal')) closeUserModal(); });

$('modalSaveBtn').addEventListener('click', async () => {
  const btn = $('modalSaveBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Saving…';
  $('modalAlert').classList.add('hidden');

  const fd = new FormData();
  fd.append('name',     $('mName').value);
  fd.append('email',    $('mEmail').value);
  fd.append('password', $('mPassword').value);
  fd.append('role_id',  $('mRole').value);

  let url;
  if (editUserId) {
    fd.append('action', 'update');
    fd.append('id', editUserId);
    url = '/pangit/api/users.php?action=update';
  } else {
    fd.append('action', 'create');
    url = '/pangit/api/users.php?action=create';
  }

  const res  = await fetch(url, { method:'POST', body:fd });
  const data = await res.json();

  btn.disabled = false;
  btn.textContent = 'Save User';

  if (data.success) {
    toast(data.message);
    closeUserModal();
    loadUsers();
  } else {
    $('modalAlertMsg').textContent = data.message;
    $('modalAlert').classList.remove('hidden');
  }
});

// ── Delete Modal ─────────────────────────────────────────────
let deleteTargetId = null;

function confirmDelete(id, name) {
  deleteTargetId = id;
  $('deleteUserName').textContent = name;
  $('deleteModal').classList.add('show');
}

function closeDeleteModal() { $('deleteModal').classList.remove('show'); }
$('deleteModalClose').addEventListener('click', closeDeleteModal);
$('deleteCancelBtn').addEventListener('click', closeDeleteModal);
$('deleteModal').addEventListener('click', e => { if (e.target === $('deleteModal')) closeDeleteModal(); });

$('deleteConfirmBtn').addEventListener('click', async () => {
  const btn = $('deleteConfirmBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span>';

  const fd = new FormData();
  fd.append('action', 'delete');
  fd.append('id', deleteTargetId);

  const res  = await fetch('/pangit/api/users.php?action=delete', { method:'POST', body:fd });
  const data = await res.json();

  btn.disabled = false;
  btn.textContent = 'Delete User';

  if (data.success) {
    toast(data.message);
    closeDeleteModal();
    loadUsers();
  } else {
    toast(data.message, 'error');
    closeDeleteModal();
  }
});

// ── Mobile sidebar toggle ────────────────────────────────────
$('sidebarToggle').addEventListener('click', () => {
  $('sidebar').classList.toggle('open');
});

function checkMobile() {
  $('sidebarToggle').style.display = window.innerWidth <= 768 ? 'flex' : 'none';
}
checkMobile();
window.addEventListener('resize', checkMobile);

// ── Init ─────────────────────────────────────────────────────
loadDashboard();
</script>

</body>
</html>
