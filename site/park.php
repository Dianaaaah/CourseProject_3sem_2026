<?php
include "header.php";
include "db.php";

$park_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($park_id == 0) {
    header("Location: index.php");
    exit();
}

$stmt = mysqli_prepare($mysql, "SELECT * FROM parks WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $park_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$park = mysqli_fetch_assoc($result);

?>
<main class="park-page">
    <div class="park-layout">
        <div class="park-map">
            <div id="park-map"><!-- здесь будет карта --></div>
        </div>

        <div class="park-info">
            <h2>
                <?php 
                $displayName = $park['park_name'];
                $displayName = str_replace(['[', ']', '"'], '', $displayName);
                
                if (empty(trim($displayName))) {
                    echo "Площадка";
                    if (!empty($park['district'])) {
                        echo " (" . htmlspecialchars($park['district']) . ")";
                    }
                } else {
                    echo htmlspecialchars($displayName);
                }
                ?>
            </h2>
            
            <?php if ($park['location']): ?>
                <p><strong>Адрес:</strong> <?= htmlspecialchars($park['location']) ?></p>
            <?php endif; ?>
            
            <?php if ($park['district']): ?>
                <p><strong>Район:</strong> <?= htmlspecialchars($park['district']) ?></p>
            <?php endif; ?>
            
            <?php if ($park['adm_area']): ?>
                <p><strong>Округ:</strong> <?= htmlspecialchars($park['adm_area']) ?></p>
            <?php endif; ?>
            
            <?php if ($park['dog_park_area']): ?>
                <p><strong>Площадь:</strong> <?= htmlspecialchars($park['dog_park_area']) ?> кв.м</p>
            <?php endif; ?>
            
            <?php if ($park['elements']): ?>
                <p><strong>Элементы площадки:</strong> <?= htmlspecialchars($park['elements']) ?></p>
            <?php endif; ?>
            
            <?php if ($park['lighting']): ?>
                <p><strong>Освещение:</strong> <?= htmlspecialchars($park['lighting']) ?></p>
            <?php endif; ?>
            
            <?php if ($park['fencing']): ?>
                <p><strong>Ограждение:</strong> <?= htmlspecialchars($park['fencing']) ?></p>
            <?php endif; ?>
            
            <!-- переход на страницу с формой -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="review-form review-form-center">
                    <a href="add_review.php?park_id=<?= $park_id ?>" class="btn-submit">
                        Оставить отзыв о посещении
                    </a>
                </div>
            <?php else: ?>
                <div class="review-form review-form-center review-form-padding">
                    <p class="muted-brown fs-16">
                        Для оставления отзыва необходимо 
                        <a href="login.php" class="link-accent">войти</a> или 
                        <a href="reg.php" class="link-accent">зарегистрироваться</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="stats-section">
        <h3>Статистика по отзывам пользователей</h3>
        
        <div class="stats-controls">
            <select id="heatmap-parameter" class="stats-select">
                <option value="activity_level">Уровень активности</option>
                <option value="dogs_count">Количество собак</option>
                <option value="aggressive_dogs">Агрессивные собаки</option>
                <option value="children_present">Наличие детей</option>
                <option value="cleanliness">Чистота площадки</option>
                <option value="convenience">Удобство площадки</option>
            </select>
        </div>

        <!-- таблица -->
        <div id="heatmap-container">
            <p class="msg msg--p20">Загрузка данных...</p>
        </div>

        <!-- процентная статистика -->
        <div class="stats-percentages" id="stats-percentages">
        </div>
        
        <!-- информация о распределении -->
        <div id="distribution-info" class="data-source data-source-center">
        </div>
    </div>
</main>


<script>
//сохраняем данные о площадке
window.parkData = {
    id: <?= $park_id ?>,
    lat: <?= !empty($park['geo_lat']) ? floatval($park['geo_lat']) : '55.7558' ?>,
    lon: <?= !empty($park['geo_lon']) ? floatval($park['geo_lon']) : '37.6173' ?>,
    name: <?= json_encode(!empty($park['park_name']) ? trim($park['park_name'], '[]"') : 'Площадка') ?>
};

console.log('Данные парка установлены:', window.parkData);
</script>

<?php include "footer.php"; ?>
