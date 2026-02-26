<?php
require('func.php');

/* ===========================
   CONFIGURACIÓN SEGURA SESIÓN
=========================== */
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', 1);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ===========================
   SECURITY HEADERS
=========================== */
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

/* ===========================
   GENERAR CSRF TOKEN
=========================== */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ===========================
   OBTENER TAREAS DE FORMA SEGURA
=========================== */
$stmt = $conn->prepare("SELECT id, title, created_at FROM task ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Secure Todo</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.2/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body>

<div class="container p-4">
<div class="row">

<!-- ================= FORM ================= -->

<div class="col-4">

<?php if(isset($_SESSION['message'])): ?>
<div class="alert alert-<?php echo e($_SESSION['message_type']); ?>">
    <?php echo e($_SESSION['message']); ?>
</div>
<?php unset($_SESSION['message']); endif; ?>

<div class="card card-body">

<form action="tasks.php" method="POST">

<input type="hidden" name="csrf_token" 
value="<?php echo e($_SESSION['csrf_token']); ?>">

<div class="form-group">
    <input class="form-control"
           type="text"
           name="title"
           placeholder="Title"
           maxlength="255"
           required>
</div>

<input type="submit"
       class="btn btn-success mt-3"
       name="save_task"
       value="Save Todo">

</form>
</div>
</div>

<!-- ================= TABLE ================= -->

<div class="col-8">
<table class="table">
<thead>
<tr>
    <th>Title</th>
    <th>Date/Time</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<tr>
<td><?php echo e($row['title']); ?></td>
<td><?php echo e($row['created_at']); ?></td>

<td>

<!-- EDIT (solo ID casteado) -->
<a href="index.php?edid=<?php echo (int)$row['id']; ?>">
    Edit
</a>

<!-- DELETE ahora por POST + CSRF -->
<form action="tasks.php" method="POST" style="display:inline;">
    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
    <input type="hidden" name="delete_id" value="<?php echo (int)$row['id']; ?>">
    <button type="submit" class="btn btn-link text-danger p-0">
        Delete
    </button>
</form>

<!-- PDF con urlencode -->
<a href="pdf.php?title=<?php echo urlencode($row['title']); ?>" target="_blank">
    PDF
</a>

</td>
</tr>

<?php endwhile; ?>

</tbody>
</table>
</div>

</div>
</div>

</body>
</html>
