<?php
declare(strict_types=1);
session_start(); require __DIR__ . '/config.php'; require_login();
$pdo=db(); $error=''; $selectedDate=(string)($_GET['date'] ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$selectedDate)) $selectedDate=date('Y-m-d');
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $employeeId=(int)($_POST['employee_id'] ?? 0); $date=(string)($_POST['attendance_date'] ?? date('Y-m-d'));
    $status=(string)($_POST['status'] ?? 'Present'); $timeIn=trim((string)($_POST['time_in'] ?? '')); $timeOut=trim((string)($_POST['time_out'] ?? '')); $notes=trim((string)($_POST['notes'] ?? ''));
    $validStatuses=['Present','Absent','Late','On Leave','Rest Day'];
    if (!in_array($status,$validStatuses,true) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) $error='Please provide a valid date and status.';
    elseif ($employeeId<1) $error='Please select an employee.';
    elseif ($timeIn!=='' && !preg_match('/^\d{2}:\d{2}$/',$timeIn)) $error='Time in must use HH:MM format.';
    elseif ($timeOut!=='' && !preg_match('/^\d{2}:\d{2}$/',$timeOut)) $error='Time out must use HH:MM format.';
    elseif ($timeIn!=='' && $timeOut!=='' && $timeOut<$timeIn) $error='Time out is earlier than time in. For overnight shifts, record times according to the station’s agreed procedure.';
    else {
        try {
            $s=$pdo->prepare("INSERT INTO attendance (employee_id,attendance_date,status,time_in,time_out,notes,created_by) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),time_in=VALUES(time_in),time_out=VALUES(time_out),notes=VALUES(notes),created_by=VALUES(created_by)");
            $s->execute([$employeeId,$date,$status,$timeIn!==''?$timeIn:null,$timeOut!==''?$timeOut:null,$notes,(int)$_SESSION['user_id']]);
            flash('Attendance record saved.'); header('Location: attendance.php?date='.urlencode($date)); exit;
        } catch (Throwable $e) { $error='Could not save attendance. Check that the employee exists and the database is available.'; }
    }
    $selectedDate=$date;
}
$employees=$pdo->query('SELECT id,employee_no,full_name FROM employees WHERE active=1 ORDER BY full_name')->fetchAll();
$s=$pdo->prepare('SELECT a.*,e.employee_no,e.full_name FROM attendance a JOIN employees e ON e.id=a.employee_id WHERE a.attendance_date=? ORDER BY e.full_name'); $s->execute([$selectedDate]); $records=$s->fetchAll();
$flash=take_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Attendance | GASATTEND</title><link rel="stylesheet" href="style.css"><script defer src="app.js"></script></head><body><div class="app-shell">
<aside class="sidebar"><a class="brand" href="index.php"><span class="brand-mark small-mark">GA</span><span><strong>GASATTEND</strong><small>Attendance made simpler</small></span></a><nav><a class="nav-link" href="index.php">⌂ <span>Dashboard</span></a><a class="nav-link" href="employees.php">♙ <span>Employees</span></a><a class="nav-link active" href="attendance.php">▣ <span>Attendance</span></a><a class="nav-link" href="reports.php">▤ <span>Reports</span></a></nav><div class="sidebar-bottom"><span class="user-dot"><?=h(strtoupper(substr($_SESSION['full_name'] ?? 'U',0,1)))?></span><div><strong><?=h($_SESSION['full_name'] ?? 'User')?></strong><small><?=h(ucfirst($_SESSION['role'] ?? 'user'))?></small></div><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>"><button class="logout-btn">↪</button></form></div></aside>
<main class="main-content"><header class="topbar"><div><span class="eyebrow">DAILY RECORDS</span><h1>Attendance</h1></div></header>
<?php if ($flash): ?><div class="notice <?=h($flash['type'])?>"><?=h($flash['message'])?></div><?php endif; ?><?php if ($error): ?><div class="alert"><?=h($error)?></div><?php endif; ?>
<div class="content-grid attendance-grid"><section class="panel"><div class="panel-heading"><div><h2>Record attendance</h2><p class="muted">Enter one attendance record per employee per date.</p></div></div>
<?php if(!$employees): ?><div class="notice warning">Add at least one active employee before recording attendance.</div><?php endif; ?>
<form method="post" class="stack"><input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
<label>Employee<select name="employee_id" required><option value="">Select employee…</option><?php foreach($employees as $e): ?><option value="<?=(int)$e['id']?>"><?=h($e['employee_no'].' — '.$e['full_name'])?></option><?php endforeach; ?></select></label>
<label>Attendance date<input type="date" name="attendance_date" value="<?=h($selectedDate)?>" required></label>
<label>Status<select name="status" id="status-select"><option>Present</option><option>Late</option><option>Absent</option><option>On Leave</option><option>Rest Day</option></select></label>
<div class="form-row"><label>Time in<input type="time" name="time_in"></label><label>Time out<input type="time" name="time_out"></label></div>
<label>Notes (optional)<textarea name="notes" rows="3" maxlength="500" placeholder="Short note about this record"></textarea></label>
<button class="btn primary" type="submit" <?=$employees?'':'disabled'?>>Save attendance</button>
<p class="muted small">Saving an existing employee/date updates that day's record.</p></form></section>
<section class="panel"><div class="panel-heading"><div><h2>Attendance records</h2><p class="muted">Select a date to review saved entries.</p></div></div>
<form method="get" class="search-row"><input type="date" name="date" value="<?=h($selectedDate)?>"><button class="btn secondary">Show date</button></form>
<div class="table-wrap"><table><thead><tr><th>Employee</th><th>Time in</th><th>Time out</th><th>Status</th></tr></thead><tbody>
<?php foreach($records as $r): ?><tr><td><strong><?=h($r['full_name'])?></strong><small class="subcell"><?=h($r['employee_no'])?></small></td><td><?=h($r['time_in']?date('g:i A',strtotime($r['time_in'])):'—')?></td><td><?=h($r['time_out']?date('g:i A',strtotime($r['time_out'])):'—')?></td><td><span class="status <?=strtolower(str_replace(' ','-',h($r['status'])))?>"><?=h($r['status'])?></span></td></tr><?php endforeach; ?>
<?php if(!$records): ?><tr><td colspan="4">No records for this date.</td></tr><?php endif; ?></tbody></table></div></section></div>
<footer class="page-footer">GASATTEND · Student capstone prototype</footer></main></div></body></html>
