<?php
require_once 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $pass_confirm = $_POST['password_confirm'];

    if ($pass !== $pass_confirm) {
        $error = "Пароли не совпадают!";
    } else {
        try {
            // Сохраняем пароль как обычный текст
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$user, $email, $pass]);
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $error = "Ошибка: Пользователь или Email уже существует.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация | Hotel Premium</title>
    <link rel="stylesheet" href="style/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <form action="register.php" method="POST" class="auth-form">
            <h2>Регистрация</h2>
            <?php if($error): ?> <p class="error"><?= $error ?></p> <?php endif; ?>
            
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="username" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <input type="password" name="password_confirm" placeholder="Подтверждение пароля" required>
            
            <button type="submit" class="btn-main">Зарегистрироваться</button>
            <p class="auth-link">Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </form>
    </div>
</body>
</html>