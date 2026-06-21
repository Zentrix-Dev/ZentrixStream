<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require '../db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 1. Secure check for existing user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Username or Email already exists!";
    } else {
        // 2. Secure insert
        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($insert_stmt->execute()) {
            $success = "Account created! You can now login.";
        } else {
            $error = "Error creating account. Please try again.";
        }
        $insert_stmt->close();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ZENTRIX STREAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background-color: #0b0b0b; font-family: 'Inter', sans-serif; }</style>
</head>
<body class="text-white min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white/5 backdrop-blur-md border border-red-600/30 rounded-xl p-8 shadow-[0_4px_20px_rgba(0,0,0,0.5)]">
        <h2 class="text-3xl font-bold text-center mb-6 text-red-600 drop-shadow-[0_0_10px_rgba(255,0,0,0.4)] tracking-wide uppercase">Join Zentrix</h2>
        
        <?php if($error): ?>
            <div class="bg-red-500/10 border border-red-500 text-red-500 text-sm p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="bg-green-500/10 border border-green-500 text-green-400 text-sm p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Username</label>
                <input type="text" name="username" required class="w-full bg-[#111] border border-gray-800 text-white px-4 py-2.5 rounded focus:outline-none focus:border-red-600 transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Email</label>
                <input type="email" name="email" required class="w-full bg-[#111] border border-gray-800 text-white px-4 py-2.5 rounded focus:outline-none focus:border-red-600 transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Password</label>
                <input type="password" name="password" required class="w-full bg-[#111] border border-gray-800 text-white px-4 py-2.5 rounded focus:outline-none focus:border-red-600 transition">
            </div>
            
            <button type="submit" class="mt-4 w-full py-3 bg-red-600 text-white font-black uppercase tracking-wider rounded shadow-[0_0_10px_rgba(255,0,0,0.3)] hover:bg-red-700 hover:shadow-[0_0_20px_rgba(255,0,0,0.6)] transition active:scale-95">
                Create Account
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Already have an account? <a href="login.php" class="text-red-500 hover:text-red-400 hover:underline font-bold">Login</a>
        </p>
    </div>

</body>
</html>
