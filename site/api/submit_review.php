<?php
session_start();
include "../db.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('success' => false, 'message' => 'Необходима авторизация'));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(array('success' => false, 'message' => 'Неверный метод запроса'));
    exit();
}

$user_id = $_SESSION['user_id'];
$park_id = isset($_POST['park_id']) ? intval($_POST['park_id']) : 0;
$day_of_week = isset($_POST['day_of_week']) ? $_POST['day_of_week'] : '';
$time_interval = isset($_POST['time_interval']) ? $_POST['time_interval'] : '';
$dogs_count = isset($_POST['dogs_count']) ? $_POST['dogs_count'] : '';
$aggressive_dogs = isset($_POST['aggressive_dogs']) ? intval($_POST['aggressive_dogs']) : 0;
$children_present = isset($_POST['children_present']) ? intval($_POST['children_present']) : 0;
$activity_level = isset($_POST['activity_level']) ? $_POST['activity_level'] : '';
$cleanliness = isset($_POST['cleanliness']) ? $_POST['cleanliness'] : '';
$convenience = isset($_POST['convenience']) ? $_POST['convenience'] : '';

if ($park_id == 0 || empty($day_of_week) || empty($time_interval) || 
    empty($dogs_count) || empty($activity_level) || empty($cleanliness) || empty($convenience)) {
    echo json_encode(array('success' => false, 'message' => 'Заполните все обязательные поля'));
    exit();
}

$insert_query = "INSERT INTO visits (user_id, park_id, day_of_week, time_interval, dogs_count, 
                                     aggressive_dogs, children_present, activity_level, cleanliness, convenience) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($mysql, $insert_query);
mysqli_stmt_bind_param($stmt, "iisssiisss", $user_id, $park_id, $day_of_week, $time_interval, 
                      $dogs_count, $aggressive_dogs, $children_present, $activity_level, $cleanliness, $convenience);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(array('success' => true, 'message' => 'Отзыв успешно сохранен'));
} else {
    echo json_encode(array('success' => false, 'message' => 'Ошибка при сохранении отзыва'));
}

mysqli_stmt_close($stmt);
?>