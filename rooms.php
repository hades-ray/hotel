<?php
session_start();
require_once 'db.php';

// Получаем все активные номера
$rooms = $pdo->query("SELECT * FROM rooms WHERE status = 'active'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Номера и цены | Hotel Premium</title>
    <link rel="stylesheet" href="style/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">HOTEL<span>PREMIUM</span></div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="rooms.php" class="active">Номера и цены</a></li>
                    <?php if(isset($_SESSION['username'])): ?>
                        <li><a href="profile.php" class="btn-login"><?= $_SESSION['username'] ?></a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn-login">Личный кабинет</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="rooms-page">
        <div class="container">
            <h1>Наши номера</h1>
            <div class="rooms-list">
                <?php foreach ($rooms as $room): ?>
                <article class="room-card">
                    <!-- ИЗМЕНЕНО: Путь теперь ведет в локальную папку img/rooms/ -->
                    <img src="img/rooms/<?= htmlspecialchars($room['image_url']) ?>" 
                         alt="<?= htmlspecialchars($room['name']) ?>" 
                         style="width:400px; height: 300px; object-fit:cover;">
                    
                    <div class="room-info">
                        <h2><?= htmlspecialchars($room['name']) ?></h2>
                        <p class="room-meta">До <?= $room['max_guests'] ?> гостей</p>
                        <p><?= htmlspecialchars($room['description']) ?></p>
                        <div class="room-footer">
                            <div class="room-price">
                                <span>от <?= number_format($room['price'], 0, '', ' ') ?> ₽</span> / сутки
                            </div>
                            
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <a href="book.php?room_id=<?= $room['id'] ?>" class="btn-book">Забронировать</a>
                            <?php else: ?>
                                <a href="login.php" class="btn-book" onclick="alert('Пожалуйста, войдите в аккаунт для бронирования');">Забронировать</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Hotel Premium. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>