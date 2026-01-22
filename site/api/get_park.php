<?php
include "../db.php";

header('Content-Type: application/json; charset=utf-8');

$park_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($park_id == 0) {
    echo json_encode(array('error' => 'ID площадки не указан'));
    exit();
}

$query = "SELECT * FROM parks WHERE id = $park_id";
$result = mysqli_query($mysql, $query);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(array('error' => 'Площадка не найдена'));
    exit();
}

$park = mysqli_fetch_assoc($result);

echo json_encode($park, JSON_UNESCAPED_UNICODE);
?>
