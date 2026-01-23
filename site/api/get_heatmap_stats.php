<?php
include "../db.php";
header('Content-Type: application/json; charset=utf-8');

$park_id = intval($_GET['park_id'] ?? 0);
$parameter = $_GET['parameter'] ?? 'activity_level';

if ($park_id === 0) {
    echo json_encode(['error' => 'park_id missing']);
    exit;
}

$days = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
$times = ['6-8','8-10','10-12','12-14','14-16','16-18','18-20','20-22','22-6'];

$heatmap = [];
$total_visits = 0;

// Для тепловой карты используем СРЕДНЕЕ значение параметра для каждого временного интервала
foreach ($days as $day) {
    foreach ($times as $time) {
        // В зависимости от параметра считаем по-разному
        if ($parameter === 'aggressive_dogs' || $parameter === 'children_present') {
            // Для бинарных параметров - процент "Да"
            $q = "SELECT 
                    COUNT(*) as cnt,
                    SUM($parameter) as positive_count
                  FROM visits
                  WHERE park_id = $park_id
                    AND day_of_week = '$day'
                    AND time_interval = '$time'";
            
            $r = mysqli_query($mysql, $q);
            $row = mysqli_fetch_assoc($r);
            
            if (!$row || intval($row['cnt']) === 0) {
                $heatmap[$day][$time] = null;
                continue;
            }
            
            $total_visits += intval($row['cnt']);
            $percent_positive = ($row['positive_count'] / $row['cnt']) * 100;
            
            $heatmap[$day][$time] = $percent_positive > 50 ? 1 : 0;
            
        } else if ($parameter === 'activity_level') {
            $q = "SELECT 
                    COUNT(*) as cnt,
                    AVG(
                        CASE activity_level
                            WHEN 'спокойно' THEN 1
                            WHEN 'средне' THEN 3
                            WHEN 'активно' THEN 5
                        END
                    ) as avg_value
                  FROM visits
                  WHERE park_id = $park_id
                    AND day_of_week = '$day'
                    AND time_interval = '$time'";
            
            $r = mysqli_query($mysql, $q);
            $row = mysqli_fetch_assoc($r);
            
            if (!$row || intval($row['cnt']) === 0) {
                $heatmap[$day][$time] = null;
                continue;
            }
            
            $total_visits += intval($row['cnt']);
            $avg_value = floatval($row['avg_value']);
            
            if ($avg_value <= 2) $heatmap[$day][$time] = 'спокойно';
            else if ($avg_value <= 4) $heatmap[$day][$time] = 'средне';
            else $heatmap[$day][$time] = 'активно';
            
        } else if ($parameter === 'dogs_count') {
            // Для количества собак
            $q = "SELECT 
                    COUNT(*) as cnt,
                    AVG(
                        CASE dogs_count
                            WHEN '0-2' THEN 1
                            WHEN '3-5' THEN 4
                            WHEN '6+' THEN 6
                        END
                    ) as avg_value
                  FROM visits
                  WHERE park_id = $park_id
                    AND day_of_week = '$day'
                    AND time_interval = '$time'";
            
            $r = mysqli_query($mysql, $q);
            $row = mysqli_fetch_assoc($r);
            
            if (!$row || intval($row['cnt']) === 0) {
                $heatmap[$day][$time] = null;
                continue;
            }
            
            $total_visits += intval($row['cnt']);
            $avg_value = floatval($row['avg_value']);
            
            if ($avg_value <= 1.5) $heatmap[$day][$time] = '0-2';
            else if ($avg_value <= 5) $heatmap[$day][$time] = '3-5';
            else $heatmap[$day][$time] = '6+';
            
        } else if ($parameter === 'cleanliness' || $parameter === 'convenience') {
            $q = "SELECT 
                    $parameter,
                    COUNT(*) as cnt
                  FROM visits
                  WHERE park_id = $park_id
                    AND day_of_week = '$day'
                    AND time_interval = '$time'
                  GROUP BY $parameter
                  ORDER BY cnt DESC
                  LIMIT 1";
            
            $r = mysqli_query($mysql, $q);
            $row = mysqli_fetch_assoc($r);
            
            if (!$row || intval($row['cnt']) === 0) {
                $heatmap[$day][$time] = null;
                continue;
            }
            
            $total_visits += intval($row['cnt']);
            $heatmap[$day][$time] = $row[$parameter];
            
        } else {
            $q = "SELECT COUNT(*) as cnt FROM visits
                  WHERE park_id = $park_id
                    AND day_of_week = '$day'
                    AND time_interval = '$time'";
            
            $r = mysqli_query($mysql, $q);
            $row = mysqli_fetch_assoc($r);
            
            if (!$row || intval($row['cnt']) === 0) {
                $heatmap[$day][$time] = null;
                continue;
            }
            
            $total_visits += intval($row['cnt']);
            $heatmap[$day][$time] = intval($row['cnt']);
        }
    }
}

//процентное распределение для выбранного параметра
$percentages = [];

if ($parameter == 'aggressive_dogs' || $parameter == 'children_present') {
    $query = "SELECT $parameter, COUNT(*) as count 
              FROM visits 
              WHERE park_id = $park_id 
              GROUP BY $parameter";
    $result = mysqli_query($mysql, $query);
    
    $total = 0;
    $values = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $values[$row[$parameter]] = intval($row['count']);
        $total += intval($row['count']);
    }
    
    foreach ($values as $value => $count) {
        $percentages[] = [
            'label' => $value ? 'Да' : 'Нет',
            'value' => $total > 0 ? round(($count / $total) * 100, 1) : 0
        ];
    }
} else {
    $allowed_parameters = ['activity_level', 'dogs_count', 'cleanliness', 'convenience'];
    if (!in_array($parameter, $allowed_parameters)) {
        $parameter = 'activity_level';
    }
    
    $query = "SELECT $parameter, COUNT(*) as count 
              FROM visits 
              WHERE park_id = $park_id 
              GROUP BY $parameter
              ORDER BY FIELD($parameter, 
                CASE WHEN '$parameter' = 'activity_level' THEN 'спокойно' END,
                CASE WHEN '$parameter' = 'activity_level' THEN 'средне' END,
                CASE WHEN '$parameter' = 'activity_level' THEN 'активно' END,
                CASE WHEN '$parameter' = 'dogs_count' THEN '0-2' END,
                CASE WHEN '$parameter' = 'dogs_count' THEN '3-5' END,
                CASE WHEN '$parameter' = 'dogs_count' THEN '6+' END,
                CASE WHEN '$parameter' = 'cleanliness' THEN 'отлично' END,
                CASE WHEN '$parameter' = 'cleanliness' THEN 'хорошо' END,
                CASE WHEN '$parameter' = 'cleanliness' THEN 'удовлетворительно' END,
                CASE WHEN '$parameter' = 'cleanliness' THEN 'плохо' END,
                CASE WHEN '$parameter' = 'convenience' THEN 'отлично' END,
                CASE WHEN '$parameter' = 'convenience' THEN 'хорошо' END,
                CASE WHEN '$parameter' = 'convenience' THEN 'удовлетворительно' END,
                CASE WHEN '$parameter' = 'convenience' THEN 'плохо' END
              )";
    
    $result = mysqli_query($mysql, $query);
    
    $total = 0;
    $values = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $values[$row[$parameter]] = intval($row['count']);
        $total += intval($row['count']);
    }
    
    foreach ($values as $value => $count) {
        $percentages[] = [
            'label' => $value,
            'value' => $total > 0 ? round(($count / $total) * 100, 1) : 0
        ];
    }
}

echo json_encode([
    'heatmap' => $heatmap,
    'percentages' => $percentages,
    'total_visits' => $total_visits,
    'parameter' => $parameter
], JSON_UNESCAPED_UNICODE);
?>

