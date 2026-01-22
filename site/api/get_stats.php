<?php
include "../db.php";

header('Content-Type: application/json; charset=utf-8');

$park_id = isset($_GET['park_id']) ? intval($_GET['park_id']) : 0;
$parameter = isset($_GET['parameter']) ? $_GET['parameter'] : 'activity_level';

if ($park_id == 0) {
    echo json_encode(array('error' => 'ID площадки не указан'));
    exit();
}

$days = array('Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс');
$stats = array();

foreach ($days as $day) {
    $query = "SELECT COUNT(*) as count FROM visits WHERE park_id = $park_id AND day_of_week = '$day'";
    $result = mysqli_query($mysql, $query);
    $row = mysqli_fetch_assoc($result);
    $stats[$day] = intval($row['count']);
}

$percentages = array();

if ($parameter == 'aggressive_dogs' || $parameter == 'children_present') {
    $query = "SELECT $parameter, COUNT(*) as count 
              FROM visits 
              WHERE park_id = $park_id 
              GROUP BY $parameter";
    $result = mysqli_query($mysql, $query);
    
    $total = 0;
    $values = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $values[$row[$parameter]] = intval($row['count']);
        $total += intval($row['count']);
    }
    
    foreach ($values as $value => $count) {
        $percentages[] = array(
            'label' => $value ? 'Да' : 'Нет',
            'value' => $total > 0 ? round(($count / $total) * 100, 1) : 0
        );
    }
} else {
    $allowed_parameters = array('activity_level', 'dogs_count', 'cleanliness', 'convenience');
    if (!in_array($parameter, $allowed_parameters)) {
        $parameter = 'activity_level';
    }
    
    $query = "SELECT $parameter, COUNT(*) as count 
              FROM visits 
              WHERE park_id = $park_id 
              GROUP BY $parameter";
    $result = mysqli_query($mysql, $query);
    
    $total = 0;
    $values = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $values[$row[$parameter]] = intval($row['count']);
        $total += intval($row['count']);
    }
    
    foreach ($values as $value => $count) {
        $percentages[] = array(
            'label' => $value,
            'value' => $total > 0 ? round(($count / $total) * 100, 1) : 0
        );
    }
}

$time_stats = array();
$time_intervals = array('6-8', '8-10', '10-12', '12-14', '14-16', '16-18', '18-20', '20-22');

foreach ($time_intervals as $interval) {
    $query = "SELECT COUNT(*) as count FROM visits WHERE park_id = $park_id AND time_interval = '$interval'";
    $result = mysqli_query($mysql, $query);
    $row = mysqli_fetch_assoc($result);
    $time_stats[$interval] = intval($row['count']);
}

echo json_encode(array(
    'days_stats' => $stats,
    'time_stats' => $time_stats,
    'percentages' => $percentages
), JSON_UNESCAPED_UNICODE);
?>