<?php
include_once __DIR__ . "/../includes/session.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "hospital") {
    header("Location: ../public/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard</title>
</head>
<body>
    <h1>This is the Hospital Dashboard</h1>
    <form action="../auth/logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
