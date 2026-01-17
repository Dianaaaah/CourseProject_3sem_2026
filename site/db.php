<?php
try {
    $mysql = mysqli_connect("localhost", "root", "", "dog_parks_monitoring");
    
    mysqli_set_charset($mysql, "utf8");
    
} catch (mysqli_sql_exception $e) {
    die("Ошибка подключения к MySQL: " . $e->getMessage());
}
?>
