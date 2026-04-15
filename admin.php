<?php
session_start();
require_once 'db.php';

// Защита: если не админ — выкидываем на логин
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ЛОГИКА СМЕНЫ СТАТУСА
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'confirm') {
        $stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'Оплачено' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($action === 'cancel') {
        $stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'Отменено' WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    // Перенаправляем обратно, чтобы в адресной строке не висели параметры
    header("Location: admin.php");
    exit;
}

// 1. ЛОГИКА ИЗМЕНЕНИЯ ЦЕНЫ
if (isset($_POST['update_price'])) {
    $room_id = (int)$_POST['room_id'];
    $new_price = (float)$_POST['new_price'];
    
    $stmt = $pdo->prepare("UPDATE rooms SET price = ? WHERE id = ?");
    $stmt->execute([$new_price, $room_id]);
    header("Location: admin.php");
    exit;
}

// 2. ЛОГИКА ПЕРЕКЛЮЧЕНИЯ СТАТУСА (РЕМОНТ / ДОСТУПЕН)
if (isset($_GET['toggle_room_status']) && isset($_GET['room_id'])) {
    $room_id = (int)$_GET['room_id'];
    $current_status = $_GET['toggle_room_status'];
    
    // Меняем статус на противоположный
    $new_status = ($current_status === 'active') ? 'repair' : 'active';
    
    $stmt = $pdo->prepare("UPDATE rooms SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $room_id]);
    header("Location: admin.php");
    exit;
}

// Получаем данные для статистики и таблиц
$rooms = $pdo->query("SELECT * FROM rooms")->fetchAll();
$bookings = $pdo->query("
    SELECT b.*, r.name as room_name 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    ORDER BY b.id DESC
")->fetchAll();
$today_checkins = $pdo->query("SELECT COUNT(*) FROM bookings WHERE check_in = CURDATE() AND payment_status != 'Отменено'")->fetchColumn();




$today = date('Y-m-d');

// 1. Считаем общее количество активных номеров (которые не на ремонте)
$stmtTotal = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'active'");
$totalActiveRooms = $stmtTotal->fetchColumn();

// 2. Считаем количество занятых номеров на сегодня
// Номер считается занятым, если сегодня >= дата_заезда И сегодня < дата_выезда
// И бронь не отменена
$stmtOccupied = $pdo->prepare("
    SELECT COUNT(DISTINCT room_id) FROM bookings 
    WHERE payment_status != 'Отменено' 
    AND ? >= check_in 
    AND ? < check_out
");
$stmtOccupied->execute([$today, $today]);
$occupiedRoomsCount = $stmtOccupied->fetchColumn();

// 3. Вычисляем процент загрузки
$occupancyPercentage = 0;
if ($totalActiveRooms > 0) {
    $occupancyPercentage = round(($occupiedRoomsCount / $totalActiveRooms) * 100);
}

// 4. Дополнительно: Считаем доход за текущий месяц (для блока статистики)
$currentMonth = date('m');
$stmtIncome = $pdo->prepare("
    SELECT SUM(total_price) FROM bookings 
    WHERE payment_status = 'Оплачено' 
    AND MONTH(check_in) = ?
");
$stmtIncome->execute([$currentMonth]);
$monthlyIncome = $stmtIncome->fetchColumn() ?: 0;

// ... существующий код получения данных для таблиц ...
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
                <h3>Доход (в этом месяце)</h3>
                <p><?= number_format($monthlyIncome, 0, '.', ' ') ?> ₽</p>
            </div>
            <div class="stat-card">
                <h3>Загрузка отеля</h3>
                <!-- Наш динамический расчет -->
                <p><?= $occupancyPercentage ?>%</p>
                <small style="color: #777;">
                    Занято: <?= $occupiedRoomsCount ?> из <?= $totalActiveRooms ?> ном.
                </small>
            </div>
        </div>



        <!-- Управление номерами -->
        <section class="admin-section">
            <h2>Управление номерами</h2>
            <table>
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Цена за сутки (₽)</th>
                        <th>Текущий статус</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td>
                            <!-- Форма для быстрого изменения цены -->
                            <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                                <input type="number" name="new_price" value="<?= (int)$r['price'] ?>" 
                                       style="width: 80px; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                                <button type="submit" name="update_price" class="btn-action btn-edit" style="padding: 5px 8px;">ОК</button>
                            </form>
                        </td>
                        <td>
                            <?php if ($r['status'] == 'active'): ?>
                                <span class="status-badge status-paid">Доступен</span>
                            <?php else: ?>
                                <span class="status-badge status-cancel">На ремонте</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['status'] == 'active'): ?>
                                <!-- Ссылка для перевода в ремонт -->
                                <a href="admin.php?toggle_room_status=active&room_id=<?= $r['id'] ?>" 
                                   class="btn-action btn-danger" style="text-decoration:none;">В ремонт</a>
                            <?php else: ?>
                                <!-- Ссылка для открытия номера -->
                                <a href="admin.php?toggle_room_status=repair&room_id=<?= $r['id'] ?>" 
                                   class="btn-action" style="text-decoration:none; background: #2e7d32; color: #fff;">Открыть</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
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
                        <th>Сумма</th>
                        <th>Статус</th>
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
                        <td><strong><?= number_format($b['total_price'], 0, '.', ' ') ?> ₽</strong></td>
                        <td>
                            <!-- Добавляем цвет для статуса "Отменено" -->
                            <?php 
                                $class = '';
                                if ($b['payment_status'] == 'Оплачено') $class = 'status-paid';
                                if ($b['payment_status'] == 'Ожидает') $class = 'status-wait';
                                if ($b['payment_status'] == 'Отменено') $class = 'status-cancel';
                            ?>
                            <span class="status-badge <?= $class ?>"><?= $b['payment_status'] ?></span>
                        </td>
                        <td>
                            <!-- Ссылки-кнопки для управления -->
                            <?php if ($b['payment_status'] !== 'Отменено'): ?>
                                <?php if ($b['payment_status'] === 'Ожидает'): ?>
                                    <a href="admin.php?action=confirm&id=<?= $b['id'] ?>" class="btn-action btn-edit" style="text-decoration:none;">Оплачено</a>
                                <?php endif; ?>
                                <a href="admin.php?action=cancel&id=<?= $b['id'] ?>" class="btn-action btn-danger" style="text-decoration:none;" onclick="return confirm('Отменить бронирование?')">Отмена</a>
                            <?php else: ?>
                                <span style="color:#999; font-size:12px;">Действий нет</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

</body>
</html>