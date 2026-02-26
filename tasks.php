<?php

require('func.php');

/* ===========================
   CONFIGURACIÓN SESIÓN SEGURA
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
   FUNCIÓN ESCAPE
=========================== */
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/* ===========================
   VALIDAR CSRF
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("CSRF validation failed");
    }
}

/* ===========================
   GUARDAR / ACTUALIZAR TAREA
=========================== */
if (isset($_POST['save_task'])) {

    if (empty($_POST['title'])) {
        die("Title is required");
    }

    $title = trim($_POST['title']);

    if (strlen($title) > 255) {
        die("Title too long");
    }

    // UPDATE
    if (!empty($_POST['edid'])) {

        $edid = (int)$_POST['edid'];

        $stmt = $conn->prepare("UPDATE task SET title = ? WHERE id = ?");
        $stmt->bind_param("si", $title, $edid);
        $result = $stmt->execute();

    } 
    // INSERT
    else {

        $stmt = $conn->prepare("INSERT INTO task(title) VALUES (?)");
        $stmt->bind_param("s", $title);
        $result = $stmt->execute();
    }

    if (!$result) {
        die("Database error");
    }

    $_SESSION['message'] = 'Task saved successfully';
    $_SESSION['message_type'] = 'success';

}

/* ===========================
   BORRAR TAREA (POST ONLY)
=========================== */
elseif (isset($_POST['delete_id'])) {

    $id = (int)$_POST['delete_id'];

    if ($id <= 0) {
        die("Invalid ID");
    }

    $stmt = $conn->prepare("DELETE FROM task WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();

    if (!$result) {
        die("Database error");
    }

    $_SESSION['message'] = 'Task removed successfully';
    $_SESSION['message_type'] = 'warning';
}

/* ===========================
   REDIRECT SEGURO
=========================== */
header('Location: index.php');
exit;
