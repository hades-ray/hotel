<?php
session_start();
require_once 'db.php';

// Защита: если не админ — выкидываем на логин
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Получаем данные для статистики и таблиц
$rooms = $pdo->query("SELECT * FROM rooms")->fetchAll();
$bookings = $pdo->query("
    SELECT b.*, r.name as room_name 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    ORDER BY b.check_in DESC
")->fetchAll();
$today_checkins = $pdo->query("SELECT COUNT(*) FROM bookings WHERE check_in = CURDATE()")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора | Hotel Premium</title>
    <link rel="stylesheet" href="style/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Специфические стили для админки */
        .admin-body { background: #f0f2f5; font-family: 'Montserrat', sans-serif; }
        .admin-header { background: #333; color: #fff; padding: 15px 0; margin-bottom: 30px; }
        .admin-header .container { display: flex; justify-content: space-between; align-items: center; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 5px solid #bfa37e; }
        .stat-card h3 { font-size: 14px; color: #777; margin-bottom: 10px; }
        .stat-card p { font-size: 24px; font-weight: 600; }

        .admin-section { background: #fff; padding: 25px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .admin-section h2 { margin-bottom: 20px; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        table th { background: #f9f9f9; color: #666; }

        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .status-paid { background: #e8f5e9; color: #2e7d32; }
        .status-wait { background: #fff3e0; color: #ef6c00; }

        .btn-action { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-edit { background: #bfa37e; color: #fff; text-decoration: none; }
        .btn-danger { background: #e57373; color: #fff; }

        /* Шахматка (Календарь) */
        .calendar-grid { display: grid; grid-template-columns: repeat(15, 1fr); gap: 5px; margin-top: 15px; }
        .cal-day { height: 40px; border: 1px solid #eee; display: flex; align-items: center; justify-content: center; font-size: 10px; border-radius: 3px; }
        .cal-busy { background: #e57373; color: #fff; }
        .cal-free { background: #a5d6a7; color: #fff; }
    </style>
</head>
<body class="admin-body">

    <!-- Верхняя панель -->
    <header class="admin-header">
        <div class="container">
            <div class="admin-info">
                <strong>Администратор: <?= $_SESSION['username'] ?></strong> | 
                <span>Дата: <?= date('d.m.Y') ?></span>
            </div>
            <a href="logout.php" class="btn-login" style="border-color: #fff; color: #fff; text-decoration: none;">Выйти</a>
        </div>
    </header>

    <div class="container">
        
        <!-- Блок статистики -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Заездов сегодня</h3>
                <p><?= $today_checkins ?></p>
            </div>
            <div class="stat-card">
                <h3>Доход (тек. месяц)</h3>
                <p>145 000 ₽</p>
            </div>
            <div class="stat-card">
                <h3>Загрузка отеля</h3>
                <p>75%</p>
            </div>
        </div>

        <!-- Управление бронированиями -->
        <section class="admin-section">
            <h2>Управление бронированиями</h2>
            <table>
                <thead>
                    <tr>
                        <th>Гость</th>
                        <th>Номер</th>
                        <th>Заезд</th>
                        <th>Выезд</th>
                        <th>Сумма</th> <!-- Новый столбец -->
                        <th>Оплата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['guest_name']) ?></td>
                        <td><?= $b['room_name'] ?></td>
                        <td><?= $b['check_in'] ?></td>
                        <td><?= $b['check_out'] ?></td>
                        <td><strong><?= number_format($b['total_price'], 0, '.', ' ') ?> ₽</strong></td> <!-- Вывод суммы -->
                        <td><span class="status-badge <?= $b['payment_status'] == 'Оплачено' ? 'status-paid' : 'status-wait' ?>"><?= $b['payment_status'] ?></span></td>
                        <td>
                            <button class="btn-action btn-edit">Оплачено</button>
                            <button class="btn-action btn-danger">Отмена</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Управление номерами -->
        <section class="admin-section">
            <h2>Управление номерами</h2>
            <table>
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Цена за сутки</th>
                        <th>Статус</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $r): ?>
                    <tr>
                        <td><?= $r['name'] ?></td>
                        <td><strong><?= number_format($r['price'], 0, '.', ' ') ?> ₽</strong></td>
                        <td><?= $r['status'] == 'active' ? 'Доступен' : 'Ремонт' ?></td>
                        <td>
                            <button class="btn-action btn-edit">Изменить цену</button>
                            <button class="btn-action btn-danger"><?= $r['status'] == 'active' ? 'В ремонт' : 'Активировать' ?></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Календарь занятости (упрощенная шахматка) -->
        <section class="admin-section">
            <h2>Календарь занятости (ближайшие 15 дней)</h2>
            <div class="calendar-grid">
                <?php 
                // Просто пример генерации сетки
                for ($i = 1; $i <= 45; $i++) {
                    $is_busy = ($i % 7 == 0 || $i % 5 == 0);
                    echo '<div class="cal-day ' . ($is_busy ? 'cal-busy' : 'cal-free') . '">' . ($i > 15 ? ($i % 15 + 1) : $i) . '</div>';
                }
                ?>
            </div>
            <p style="margin-top: 15px; font-size: 12px; color: #777;">* Красный — занято, Зеленый — свободно</p>
        </section>

    </div>

</body>
</html>