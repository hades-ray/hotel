<?php
session_start();
require_once 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Ищем пользователя по логину
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $user_data = $stmt->fetch();

    // Прямое сравнение строк (без password_verify)
    if ($user_data && $pass === $user_data['password']) {
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['role'] = $user_data['role'];

    if ($user_data['role'] == 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: profile.php"); // Перенаправляем обычного пользователя в профиль
    }
        exit;
    } else {
        $error = "Неверный логин или пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход | Hotel Premium</title>
    <link rel="stylesheet" href="style/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <form action="login.php" method="POST" class="auth-form">
            <h2>Вход</h2>
            <?php if($error): ?> <p class="error"><?= $error ?></p> <?php endif; ?>
            
            <input type="text" name="username" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            
            <button type="submit" class="btn-main">Войти</button>
            <p class="auth-link">Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a></p>
        </form>
    </div>
</body>
</html>