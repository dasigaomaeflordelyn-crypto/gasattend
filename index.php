<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/config.php';
require_login();
$pdo = db();
$today = date('Y-m-d');
$totalEmployees = (int)$pdo->query('SELECT COUNT(*) FROM employees WHERE active = 1')->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ? AND status IN ('Present','Late')");
$stmt->execute([$today]); $present = (int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ? AND status = 'Absent'");
$stmt->execute([$today]); $absent = (int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT a.*, e.employee_no, e.full_name FROM attendance a JOIN employees e ON e.id=a.employee_id WHERE a.attendance_date=? ORDER BY a.updated_at DESC LIMIT 8");
$stmt->execute([$today]); $recent = $stmt->fetchAll();
$flash = take_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Dashboard | GASATTEND</title><link rel="stylesheet" href="style.css"><script defer src="app.js"></script></head>
<body>
<div class="app-shell">
<aside class="sidebar">
<a class="brand" href="index.php"><span class="brand-mark small-mark">GA</span><span><strong>GASATTEND</strong><small>Attendance made simpler</small></span></a>
<nav>
<a class="nav-link active" href="index.php">⌂ <span>Dashboard</span></a>
<a class="nav-link" href="employees.php">♙ <span>Employees</span></a>
<a class="nav-link" href="attendance.php">▣ <span>Attendance</span></a>
<a class="nav-link" href="reports.php">▤ <span>Reports</span></a>
</nav>
<div class="sidebar-bottom"><span class="user-dot"><?=h(strtoupper(substr($_SESSION['full_name'] ?? 'U',0,1)))?></span><div><strong><?=h($_SESSION['full_name'] ?? 'User')?></strong><small><?=h(ucfirst($_SESSION['role'] ?? 'user'))?></small></div>
<form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>"><button class="logout-btn" title="Log out">↪</button></form></div>
</aside>
<main class="main-content">
<header class="topbar"><div><span class="eyebrow">ATTENDANCE MANAGEMENT</span><h1>Dashboard</h1></div><div class="top-date"><?=h(date('l, F j, Y'))?></div></header>
<?php if ($flash): ?><div class="notice <?=h($flash['type'])?>"><?=h($flash['message'])?></div><?php endif; ?>
<section class="welcome-banner"><div><p class="eyebrow light">GASATTEND OVERVIEW</p><h2>Hello, <?=h(explode(' ', trim($_SESSION['full_name'] ?? 'there'))[0])?>!</h2><p>Here's the attendance overview for today.</p></div><div class="banner-icon">✓</div></section>
<section class="stats-grid">
<article class="stat-card"><span class="stat-icon blue">♙</span><div><p>Active employees</p><strong><?= $totalEmployees ?></strong></div><a href="employees.php">Manage →</a></article>
<article class="stat-card"><span class="stat-icon green">✓</span><div><p>Present / Late today</p><strong><?= $present ?></strong></div><a href="attendance.php?date=<?=h($today)?>">View records →</a></article>
<article class="stat-card"><span class="stat-icon red">!</span><div><p>Marked absent today</p><strong><?= $absent ?></strong></div><a href="attendance.php?date=<?=h($today)?>">View records →</a></article>
</section>
<section class="content-grid">
<article class="panel wide"><div class="panel-heading"><div><h2>Today's attendance</h2><p class="muted">Most recently updated records for <?=h(date('M j, Y'))?></p></div><a class="text-link" href="attendance.php?date=<?=h($today)?>">View all →</a></div>
<?php if (!$recent): ?><div class="empty-state"><span>▣</span><h3>No attendance records yet</h3><p>Start by recording today's attendance.</p><a class="btn primary" href="attendance.php">Record attendance</a></div>
<?php else: ?><div class="table-wrap"><table><thead><tr><th>Employee</th><th>Time in</th><th>Time out</th><th>Status</th></tr></thead><tbody>
<?php foreach ($recent as $row): ?><tr><td><strong><?=h($row['full_name'])?></strong><small class="subcell"><?=h($row['employee_no'])?></small></td><td><?=h($row['time_in'] ? date('g:i A', strtotime($row['time_in'])) : '—')?></td><td><?=h($row['time_out'] ? date('g:i A', strtotime($row['time_out'])) : '—')?></td><td><span class="status <?=strtolower(str_replace(' ','-',h($row['status'])))?>"><?=h($row['status'])?></span></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></article>
<article class="panel"><div class="panel-heading"><div><h2>Quick actions</h2><p class="muted">Common tasks</p></div></div><div class="quick-actions">
<a href="attendance.php" class="quick-action"><span class="quick-icon blue">＋</span><span><strong>Record attendance</strong><small>Add or update a daily entry</small></span><b>›</b></a>
<a href="employees.php" class="quick-action"><span class="quick-icon purple">♙</span><span><strong>Manage employees</strong><small>Add or update employee profiles</small></span><b>›</b></a>
<a href="reports.php" class="quick-action"><span class="quick-icon green">▤</span><span><strong>Generate reports</strong><small>Review and print a date range</small></span><b>›</b></a>
</div><div class="help-box"><strong>Remember</strong><p>Check entries before generating a report. Follow the station's approved attendance rules.</p></div></article>
</section>
<footer class="page-footer">GASATTEND · Student capstone prototype</footer>
</main></div>
</body></html>
