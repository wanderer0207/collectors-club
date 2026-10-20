<?php
    session_start();

    if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
    }

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ORIPA SITE - MY PAGE</title>

    <link href="../css/login.css" rel="stylesheet">

    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="copyrights" content="">
    <meta name="robots" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>


<body>
<main class="login-container">
    
    <div class="login">
        
    <h2>マイページ</h2>
    
    <p>ようこそ <?= htmlspecialchars($_SESSION["name"]) ?>さん</p>
    
    <p>所持ポイント <?= htmlspecialchars($_SESSION["point"]) ?>　pt</p>
    
        <p>
            <a href= "../index.php">オリパを引く</a><br>
            <a href= "#####">オリパ履歴</a><br>
            <a href= "logout.php">ログアウト</a><br>
        </p>
        
    </div>

</main>


<footer>

    <p>
        copyright &copy; 2026 oripa
    </p>

</footer>

<script src="../js/script.js"></script>

</body>
</html>