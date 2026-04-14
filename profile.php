<?php
session_start();
require_once 'db.php';

// Если не авторизован — на страницу входа
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем информацию о пользователе
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Получаем бронирования этого пользователя
$stmt = $pdo->prepare("
    SELECT b.*, r.name as room_name, total_price 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.user_id = ?
");
$stmt->execute([$user_id]);
$my_bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мой профиль | Hotel Premium</title>
    <link rel="stylesheet" href="style/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .profile-container { padding: 50px 0; min-height: 80vh; }
        .profile-card { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .profile-card h2 { margin-bottom: 20px; color: #333; }
        .user-info p { margin-bottom: 10px; font-size: 16px; }
        .user-info span { font-weight: 600; color: #bfa37e; }
        
        .booking-item { border-left: 4px solid #bfa37e; background: #f9f9f9; padding: 20px; margin-bottom: 15px; border-radius: 0 8px 8px 0; display: flex; justify-content: space-between; align-items: center; }
        .booking-details h4 { font-size: 18px; margin-bottom: 5px; }
        .booking-details p { font-size: 14px; color: #666; }
        .booking-status { font-weight: 600; font-size: 14px; color: #2e7d32; }

        .nav-links { margin-bottom: 20px; }
        .nav-links a { text-decoration: none; color: #bfa37e; font-weight: 600; margin-right: 20px; }
    </style>
</head>
<body style="background: #f4f4f4;">

    <header class="header">
        <div class="container">
            <div class="logo">HOTEL<span>PREMIUM</span></div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">На главную</a></li>
                    <li><a href="logout.php" class="btn-login">Выйти</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container profile-container">
        <div class="nav-links">
            <a href="index.php">← Вернуться на главную</a>
        </div>

        <div class="profile-card">
            <h2>Личные данные</h2>
            <div class="user-info">
                <p>Логин: <span><?= htmlspecialchars($user['username']) ?></span></p>
                <p>Email: <span><?= htmlspecialchars($user['email']) ?></span></p>
                <p>Статус: <span>Постоянный гость</span></p>
            </div>
        </div>

        <div class="profile-card">
            <h2>Мои бронирования</h2>
            <?php if (empty($my_bookings)): ?>
                <p>У вас пока нет активных бронирований. <a href="rooms.php" style="color:#bfa37e;">Выбрать номер</a></p>
            <?php else: ?>
                <?php foreach($my_bookings as $booking): ?>
                    <div class="booking-item">
                        <div class="booking-details">
                            <h4><?= htmlspecialchars($booking['room_name']) ?></h4>
                            <p>Даты: <?= $booking['check_in'] ?> — <?= $booking['check_out'] ?></p>
                            <p>Сумма: <?= number_format($booking['total_price'], 0, '.', ' ') ?> ₽</p>
                        </div>
                        <div class="booking-status">
                            <?= $booking['payment_status'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>