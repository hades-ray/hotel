<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отель Premium | Главная</title>
    <link rel="stylesheet" href="style/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">HOTEL<span>PREMIUM</span></div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php" class="active">Главная</a></li>
                    <li><a href="rooms.php">Номера и цены</a></li>
                    
                    <!-- Логика подмены ссылки -->
                    <?php if(isset($_SESSION['username'])): ?>
                        <li><a href="profile.php" class="btn-login"><?= htmlspecialchars($_SESSION['username']) ?></a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn-login">Личный кабинет</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Главный баннер (Hero) -->
    <section class="hero">
        <div class="hero-content">
            <h1>Идеальный отдых начинается здесь</h1>
            <p>Почувствуйте уют и первоклассный сервис в самом сердце города</p>
            <a href="rooms.php" class="btn-main">Забронировать номер</a>
        </div>
    </section>

    <!-- Блок "О нас" -->
    <section class="about">
        <div class="container">
            <h2>О нашем отеле</h2>
            <div class="about-grid">
                <div class="about-text">
                    <p>Мы предлагаем нашим гостям уникальное сочетание современного комфорта и классического гостеприимства. Каждый из наших номеров спроектирован так, чтобы вы чувствовали себя как дома.</p>
                    <ul class="features">
                        <li>— Центр города</li>
                        <li>— Завтрак "шведский стол" включен</li>
                        <li>— Бесплатный высокоскоростной Wi-Fi</li>
                        <li>— SPA и фитнес-центр для гостей</li>
                    </ul>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Интерьер отеля">
                </div>
            </div>
        </div>
    </section>

    <!-- Футер -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Hotel Premium. Все права защищены.</p>
            <p>Ул. Гостеприимства, д. 10 | +7 (999) 000-00-00</p>
        </div>
    </footer>

</body>
</html>