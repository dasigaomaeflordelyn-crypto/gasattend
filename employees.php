<?php
declare(strict_types=1);
session_start(); require __DIR__ . '/config.php'; require_login();
$pdo=db(); $error=''; $edit=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $action=$_POST['action'] ?? '';
    if ($action==='save') {
        $id=(int)($_POST['id'] ?? 0); $no=trim((string)($_POST['employee_no'] ?? '')); $name=trim((string)($_POST['full_name'] ?? '')); $position=trim((string)($_POST['position'] ?? ''));
        if ($no==='' || $name==='') { $error='Employee number and full name are required.'; }
        else {
            try {
                if ($id>0) { $s=$pdo->prepare('UPDATE employees SET employee_no=?, full_name=?, position=? WHERE id=?'); $s->execute([$no,$name,$position,$id]); flash('Employee updated.'); }
                else { $s=$pdo->prepare('INSERT INTO employees (employee_no,full_name,position) VALUES (?,?,?)'); $s->execute([$no,$name,$position]); flash('Employee added.'); }
                header('Location: employees.php'); exit;
            } catch (PDOException $e) { $error=$e->getCode()==='23000'?'That employee number already exists.':'Unable to save employee.'; }
        }
    } elseif ($action==='toggle') {
        $id=(int)($_POST['id'] ?? 0); $s=$pdo->prepare('UPDATE employees SET active=IF(active=1,0,1) WHERE id=?'); $s->execute([$id]); flash('Employee status updated.'); header('Location: employees.php'); exit;
    }
}
if (isset($_GET['edit'])) { $s=$pdo->prepare('SELECT * FROM employees WHERE id=?'); $s->execute([(int)$_GET['edit']]); $edit=$s->fetch() ?: null; }
$q=trim((string)($_GET['q'] ?? ''));
$sql='SELECT * FROM employees WHERE employee_no LIKE ? OR full_name LIKE ? ORDER BY active DESC, full_name';
$s=$pdo->prepare($sql); $like='%'.$q.'%'; $s->execute([$like,$like]); $employees=$s->fetchAll(); $flash=take_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Employees | GASATTEND</title><link rel="stylesheet" href="style.css"><script defer src="app.js"></script></head><body><div class="app-shell">
<aside class="sidebar"><a class="brand" href="index.php"><span class="brand-mark small-mark">GA</span><span><strong>GASATTEND</strong><small>Attendance made simpler</small></span></a><nav><a class="nav-link" href="index.php">⌂ <span>Dashboard</span></a><a class="nav-link active" href="employees.php">♙ <span>Employees</span></a><a class="nav-link" href="attendance.php">▣ <span>Attendance</span></a><a class="nav-link" href="reports.php">▤ <span>Reports</span></a></nav><div class="sidebar-bottom"><span class="user-dot"><?=h(strtoupper(substr($_SESSION['full_name'] ?? 'U',0,1)))?></span><div><strong><?=h($_SESSION['full_name'] ?? 'User')?></strong><small><?=h(ucfirst($_SESSION['role'] ?? 'user'))?></small></div><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>"><button class="logout-btn">↪</button></form></div></aside>
<main class="main-content"><header class="topbar"><div><span class="eyebrow">PEOPLE DIRECTORY</span><h1>Employees</h1></div><a href="attendance.php" class="btn primary">＋ Record attendance</a></header>
<?php if ($flash): ?><div class="notice <?=h($flash['type'])?>"><?=h($flash['message'])?></div><?php endif; ?><?php if ($error): ?><div class="alert"><?=h($error)?></div><?php endif; ?>
<div class="content-grid employee-grid"><section class="panel"><div class="panel-heading"><div><h2><?= $edit?'Edit employee':'Add employee' ?></h2><p class="muted">Keep employee details up to date.</p></div></div>
<form method="post" class="stack"><input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=h((string)($edit['id']??0))?>">
<label>Employee number<input name="employee_no" required maxlength="30" value="<?=h($edit['employee_no']??'')?>" placeholder="e.g. GS-001"></label>
<label>Full name<input name="full_name" required maxlength="120" value="<?=h($edit['full_name']??'')?>"></label>
<label>Position / role<input name="position" maxlength="80" value="<?=h($edit['position']??'')?>" placeholder="e.g. Cashier"></label>
<button class="btn primary" type="submit">Save employee</button><?php if ($edit): ?><a class="btn secondary" href="employees.php">Cancel edit</a><?php endif; ?></form></section>
<section class="panel"><div class="panel-heading"><div><h2>Employee list</h2><p class="muted"><?=count($employees)?> matching records</p></div></div>
<form method="get" class="search-row"><input name="q" value="<?=h($q)?>" placeholder="Search name or employee no."><button class="btn secondary">Search</button></form>
<div class="table-wrap"><table><thead><tr><th>Employee</th><th>Position</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($employees as $e): ?><tr><td><strong><?=h($e['full_name'])?></strong><small class="subcell"><?=h($e['employee_no'])?></small></td><td><?=h($e['position'] ?: '—')?></td><td><span class="status <?=$e['active']?'present':'absent'?>"><?=$e['active']?'Active':'Inactive'?></span></td><td class="actions"><a class="text-link" href="employees.php?edit=<?=(int)$e['id']?>">Edit</a><form method="post" class="inline-form" data-confirm="Change this employee's active status?"><input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=(int)$e['id']?>"><button class="link-button" type="submit"><?=$e['active']?'Deactivate':'Activate'?></button></form></td></tr><?php endforeach; ?>
<?php if(!$employees): ?><tr><td colspan="4">No employees found.</td></tr><?php endif; ?></tbody></table></div></section></div>
<footer class="page-footer">GASATTEND · Student capstone prototype</footer></main></div></body></html>
