<?php
session_start();
// ডাটাবেস কানেকশন
$conn = new mysqli("localhost", "root", "", "smart_grid");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // SQL ইনজেকশন থেকে বাঁচার জন্য স্ট্রিং এস্কেপ করা
    $user = $conn->real_escape_string($user);
    $pass = $conn->real_escape_string($pass);

    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['user'] = $user; // সেশন ভেরিয়েবলে ইউজার সেট করা
        header("Location: index.php"); // ড্যাশবোর্ডে নিয়ে যাবে
        exit();
    } else {
        echo "<script>alert('Invalid Username or Password!'); window.location='login.php';</script>";
    }
}
?>