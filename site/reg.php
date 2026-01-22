<?php
include "header.php";
include "db.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $consent = isset($_POST['consent']) ? $_POST['consent'] : false;

    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = "Заполните все поля";
    }
    elseif (!$consent) {
        $error = "Необходимо согласие на обработку персональных данных";
    }
    elseif ($password !== $password_confirm) {
        $error = "Пароли не совпадают";
    }
    elseif (strlen($password) < 6) {
        $error = "Пароль должен содержать минимум 6 символов";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный email адрес";
    }
    else {
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($mysql, $check_query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Пользователь с таким именем или email уже существует";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_query = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($mysql, $insert_query);
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password_hash);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Регистрация успешна! Теперь вы можете войти.";
                $_POST = array();
            } else {
                $error = "Ошибка при регистрации. Попробуйте позже.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<main>
    <div class="form-container">
        <h1>Регистрация</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="success-message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <input 
                type="text" 
                name="username" 
                placeholder="Имя пользователя" 
                required
                value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
            >
            <input 
                type="email" 
                name="email" 
                placeholder="Email" 
                required
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
            >
            <input 
                type="password" 
                name="password" 
                placeholder="Пароль (минимум 6 символов)" 
                required
            >
            <input 
                type="password" 
                name="password_confirm" 
                placeholder="Подтвердите пароль" 
                required
            >
            
            <div class="consent-checkbox">
                <input 
                    type="checkbox" 
                    name="consent" 
                    id="consent" 
                    required
                >
                <label for="consent">
                    Я согласен(а) на обработку моих персональных данных в соответствии с 
                    <a href="https://www.consultant.ru/cons/cgi/online.cgi?req=doc&base=LAW&n=499769&dst=100001#eU1Dn8VoF8LKOqYr" target="_blank">Законом о персональных данных</a> 
                    (Федеральный закон от 27.07.2006 N 152-ФЗ (ред. от 24.06.2025) "О персональных данных").
                </label>
            </div>

            <button type="submit">Зарегистрироваться</button>
        </form>

        <p>
            Уже есть аккаунт? <a href="login.php">Войти</a>
        </p>
    </div>
</main>

<?php include "footer.php"; ?>
