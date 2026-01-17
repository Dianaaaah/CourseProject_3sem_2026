<?php
// Начинаем сессию для хранения данных пользователя
session_start();
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мониторинг площадок для выгула собак в Москве</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">

    <script src="https://api-maps.yandex.ru/2.1/?apikey=<?= $YANDEX_API_KEY ?>&lang=ru_RU" type="text/javascript"></script>
</head>
<body>

<header class="header">
    <div class="logo">
        <a href="index.php">
            <span class="logo-icon">🐕</span>
            <span class="logo-text">Площадки Москвы</span>
        </a>
    </div>

    <nav class="nav">
        <a href="index.php">Главная</a>
        <a href="index.php#about">О сайте</a>
        <a href="index.php#contacts">Контакты</a>
    </nav>

    <div class="auth">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="user-info">Привет, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="logout.php" class="btn-logout">Выйти</a>
        <?php else: ?>
            <a href="login.php" class="btn-auth">Войти</a>
            <a href="reg.php" class="btn-auth btn-register">Зарегистрироваться</a>
        <?php endif; ?>
    </div>
</header>
