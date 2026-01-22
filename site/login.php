<?php
include "header.php";
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Заполните все поля";
    } else {
        $stmt = mysqli_prepare($mysql, "SELECT id, username, password_hash FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                header("Location: index.php");
                exit();
            } else {
                $error = "Неверный логин или пароль";
            }
        } else {
            $error = "Неверный логин или пароль";
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<main>
    <div class="form-container">
        <h1>Вход</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
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
                type="password" 
                name="password" 
                placeholder="Пароль" 
                required
            >
            <button type="submit">Войти</button>
        </form>

        <p>
            Еще нет аккаунта? <a href="reg.php">Зарегистрироваться</a>
        </p>
    </div>
</main>

<?php include "footer.php"; ?>
