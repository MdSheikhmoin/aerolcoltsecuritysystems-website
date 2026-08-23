<?php

session_start();

require_once __DIR__ . "/../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Username and password are required.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, username, password_hash
             FROM admin_users
             WHERE username = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if (
            $admin &&
            password_verify(
                $password,
                $admin["password_hash"]
            )
        ) {

            session_regenerate_id(true);

            $_SESSION["admin_id"] =
                $admin["id"];

            $_SESSION["admin_username"] =
                $admin["username"];

            header("Location: index.php");
            exit;

        } else {

            $error =
                "Invalid username or password.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Admin Login - Aerol Colt</title>

<style>

body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #05050A;
    color: white;
    font-family: Arial, sans-serif;
}

.login-box {
    width: 100%;
    max-width: 400px;
    padding: 35px;
    background: #0F111A;
    border: 1px solid #1E2235;
    border-radius: 14px;
    box-sizing: border-box;
}

h1 {
    margin-top: 0;
    margin-bottom: 8px;
}

p {
    color: #9CA3AF;
    margin-bottom: 25px;
}

input {
    width: 100%;
    box-sizing: border-box;
    padding: 13px;
    margin-bottom: 15px;
    background: #05050A;
    color: white;
    border: 1px solid #303548;
    border-radius: 8px;
}

button {
    width: 100%;
    padding: 13px;
    border: 0;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}

.error {
    padding: 10px;
    margin-bottom: 15px;
    background: #3b1111;
    border: 1px solid #7f1d1d;
    border-radius: 8px;
    color: #fca5a5;
}

</style>

</head>

<body>

<div class="login-box">

<h1>Aerol Colt</h1>

<p>Admin Dashboard</p>

<?php if ($error): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<form method="POST">

<input
    type="text"
    name="username"
    placeholder="Username"
    autocomplete="username"
    required
>

<input
    type="password"
    name="password"
    placeholder="Password"
    autocomplete="current-password"
    required
>

<button type="submit">
Login
</button>

</form>

</div>

</body>

</html>