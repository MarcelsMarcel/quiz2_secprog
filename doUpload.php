<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileUpload'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['fileUpload'];

    //size limit: 5 x 1024 x 1024 = 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['error'] = "File size exceeds the 5MB limit.";
        header("Location: upload.php");
        exit();
    }
    $docx = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        $docx             => 'docx',
    ];


    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $original_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!isset($allowed[$mime])) {
        $_SESSION['error'] = "Invalid file type. Only PDF, DOCX, JPG, and PNG files are allowed.";
        header("Location: upload.php");
        exit();
    }

    //randomize name for security
    $upload_dir  = __DIR__ . '/uploads/';
    $stored_name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $target_file = $upload_dir . $stored_name;
    $original_name = basename($file['name']);

    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        $_SESSION['error'] = "Failed to upload the file.";
        header("Location: upload.php");
        exit();
    }

    $conn = new mysqli('localhost', 'root', '', 'tugas2');
    if ($conn->connect_error) {
        unlink($target_file);
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare(
        "INSERT INTO files (user_id, original_name, stored_name, file_size, file_type) VALUES (?, ?, ?, ?, ?)"
    );
    $size = (int)$file['size'];
    $stmt->bind_param("issis", $user_id, $original_name, $stored_name, $size, $mime);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        $_SESSION['success'] = "File uploaded successfully.";
        header("Location: index.php");
        exit();
    }
    unlink($target_file);
    $stmt->close();
    $conn->close();
    $_SESSION['error'] = "Failed to store file information in the database.";
    header("Location: upload.php");
    exit();
}

$_SESSION['error'] = "Invalid request.";
header("Location: upload.php");
exit();