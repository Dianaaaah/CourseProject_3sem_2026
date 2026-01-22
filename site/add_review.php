<?php
include "header.php";
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$park_id = isset($_GET['park_id']) ? intval($_GET['park_id']) : 0;
if ($park_id === 0) {
    header("Location: index.php");
    exit();
}
?>

<main>
    <div class="form-container">
        <h1>Отзыв о посещении</h1>

        <form id="review-form" method="POST">
            <input type="hidden" name="park_id" value="<?= $park_id ?>">

            <div class="form-group">
                <label>День недели *</label>
                <select name="day_of_week" required>
                    <option value="">Выберите день</option>
                    <option value="Пн">Понедельник</option>
                    <option value="Вт">Вторник</option>
                    <option value="Ср">Среда</option>
                    <option value="Чт">Четверг</option>
                    <option value="Пт">Пятница</option>
                    <option value="Сб">Суббота</option>
                    <option value="Вс">Воскресенье</option>
                </select>
            </div>

            <div class="form-group">
                <label>Время посещения *</label>
                <select name="time_interval" required>
                    <option value="">Выберите время</option>
                    <option value="6-8">6:00 – 8:00</option>
                    <option value="8-10">8:00 – 10:00</option>
                    <option value="10-12">10:00 – 12:00</option>
                    <option value="12-14">12:00 – 14:00</option>
                    <option value="14-16">14:00 – 16:00</option>
                    <option value="16-18">16:00 – 18:00</option>
                    <option value="18-20">18:00 – 20:00</option>
                    <option value="20-22">20:00 – 22:00</option>
                </select>
            </div>

            <div class="form-group">
                <label>Количество собак *</label>
                <select name="dogs_count" required>
                    <option value="">Выберите количество</option>
                    <option value="0-2">0–2</option>
                    <option value="3-5">3–5</option>
                    <option value="6+">6+</option>
                </select>
            </div>

            <div class="form-group">
                <label>Агрессивные собаки *</label>
                <select name="aggressive_dogs" required>
                    <option value="">Выберите</option>
                    <option value="1">Да</option>
                    <option value="0">Нет</option>
                </select>
            </div>

            <div class="form-group">
                <label>Были дети *</label>
                <select name="children_present" required>
                    <option value="">Выберите</option>
                    <option value="1">Да</option>
                    <option value="0">Нет</option>
                </select>
            </div>

            <div class="form-group">
                <label>Активность *</label>
                <select name="activity_level" required>
                    <option value="">Выберите</option>
                    <option value="спокойно">Спокойно</option>
                    <option value="средне">Средне</option>
                    <option value="активно">Активно</option>
                </select>
            </div>

            <div class="form-group">
                <label>Чистота *</label>
                <select name="cleanliness" required>
                    <option value="">Выберите</option>
                    <option value="отлично">Отлично</option>
                    <option value="хорошо">Хорошо</option>
                    <option value="удовлетворительно">Удовлетворительно</option>
                    <option value="плохо">Плохо</option>
                </select>
            </div>

            <div class="form-group">
                <label>Удобство *</label>
                <select name="convenience" required>
                    <option value="">Выберите</option>
                    <option value="отлично">Отлично</option>
                    <option value="хорошо">Хорошо</option>
                    <option value="удовлетворительно">Удовлетворительно</option>
                    <option value="плохо">Плохо</option>
                </select>
            </div>

            <button class="btn-submit">Отправить отзыв</button>
            <div id="review-message"></div>
        </form>
    </div>
</main>

<?php include "footer.php"; ?>
