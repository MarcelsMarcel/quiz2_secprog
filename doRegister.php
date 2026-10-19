<?php
session_start();
// ur mission is after create connection to database, you need to implement the following functionalities in this file:
// 1. Create a POST request to handle registration form submission
// 2. Validate the input fields: username, email, password, confirm_password make sure confirm_password matches password
// 3. Check if the username or email already exists in the database
// 4. If validation passes, hash the password and insert the new user into the database
// 5. If registration is successful, redirect to login.php with a success message

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
    $username = $_POST['username'];
    $email = ($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: register.php");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.php");
        exit();
    }

    //connect
    $conn = new mysqli('localhost', 'root', '', 'tugas2');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //check if exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Username or email already exists.";
        header("Location: register.php");
        exit();
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        $_SESSION['success'] = "Registration successful. Please login.";
        header("Location: login.php");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: register.php");
        exit();
    }

} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: register.php");
    exit();
}