<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$room_id = $_GET['room_id'];
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    
    // Считаем количество дней на стороне PHP для записи в БД
    $d1 = new DateTime($check_in);
    $d2 = new DateTime($check_out);
    $days = $d1->diff($d2)->days;
    
    if ($days <= 0) {
        $error = "Минимальный срок бронирования — 1 сутки.";
    } else {
        $total_price = $days * $room['price'];

        // Проверка занятости
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND NOT (check_out <= ? OR check_in >= ?)");
        $stmt->execute([$room_id, $check_in, $check_out]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Этот период уже занят.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, guest_name, room_id, check_in, check_out, total_price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['username'], $room_id, $check_in, $check_out, $total_price]);
            $success = "Забронировано! Итоговая сумма: " . number_format($total_price, 0, '', ' ') . " ₽";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Бронирование | <?= $room['name'] ?></title>
    <link rel="stylesheet" href="style/main.css">
    <script>
        // Функция для динамического расчета цены
        function calculateTotal() {
            const pricePerNight = <?= $room['price'] ?>;
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            const display = document.getElementById('total_display');
            
            if (checkIn && checkOut) {
                const date1 = new Date(checkIn);
                const date2 = new Date(checkOut);
                const timeDiff = date2.getTime() - date1.getTime();
                const days = Math.ceil(timeDiff / (1000 * 3600 * 24));
                
                if (days > 0) {
                    display.innerHTML = "Количество ночей: " + days + "<br>Итого к оплате: <strong>" + (days * pricePerNight).toLocaleString() + " ₽</strong>";
                } else {
                    display.innerHTML = "<span style='color:red;'>Дата выезда должна быть позже даты заезда</span>";
                }
            }
        }
    </script>
</head>
<body class="auth-body">
    <div class="auth-container">
        <form method="POST" class="auth-form">
            <h2>Бронирование</h2>
            <?php if($error): ?> <p class="error"><?= $error ?></p> <?php endif; ?>
            <?php if($success): ?> 
                <p style="color:green; text-align:center; font-weight:600;"><?= $success ?></p>
                <a href="profile.php" class="btn-main" style="display:block; text-align:center; text-decoration:none;">В профиль</a>
            <?php else: ?>
                <p>Номер: <strong><?= $room['name'] ?></strong></p>
                <p>Цена за сутки: <strong><?= number_format($room['price'], 0, '', ' ') ?> ₽</strong></p>
                <hr><br>

                <label>Дата заезда:</label>
                <input type="date" id="check_in" name="check_in" min="<?= date('Y-m-d') ?>" required onchange="calculateTotal()">
                
                <label>Дата выезда:</label>
                <input type="date" id="check_out" name="check_out" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required onchange="calculateTotal()">
                
                <!-- Место для вывода суммы -->
                <div id="total_display" style="margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 5px; text-align: center;">
                    Выберите даты для расчета стоимости
                </div>

                <label>Количество гостей:</label>
                <select name="guests">
                    <?php for($i=1; $i<=$room['max_guests']; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> чел.</option>
                    <?php endfor; ?>
                </select>

                <button type="submit" class="btn-main">Забронировать</button>
                <a href="rooms.php" style="display:block; text-align:center; margin-top:15px; color:#777;">Назад к номерам</a>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>