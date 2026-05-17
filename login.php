<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartGrid - Login</title>
    <link rel="stylesheet" href="style.css"> <!-- তোমার আগের CSS ফাইল -->
    <style>
        body { background: #1e1e2f; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; }
        .login-box { background: #2a2a40; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); width: 350px; text-align: center; }
        .login-box h2 { color: #2ecc71; margin-bottom: 20px; }
        .login-box input { width: 100%; padding: 12px; margin: 10px 0; border-radius: 5px; border: none; background: #3d3d5c; color: white; box-sizing: border-box; }
        .login-box button { width: 100%; padding: 12px; background: #2ecc71; border: none; color: white; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .login-box button:hover { background: #27ae60; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>SmartGrid Login</h2>
        <form action="login_process.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>