<?php

    session_start();

    // 「ログアウトする」ボタンが押された場合
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
    // セッションの中身をすべて削除
    $_SESSION = [];

    // セッションCookieも削除
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // セッションを破棄
    session_destroy();

    // ログイン画面へ移動
    header("Location: login.php");
    exit;

    }
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ORIPA SITE - ログアウト</title>

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
        
    <span>ログアウト画面</span>
    
    <p>ログアウトしますか？</p>
        
        <form action="logout.php" method="POST">

            <button type="submit">ログアウトする</button>

        </form>
        
        <a href="../index.php">キャンセル</a>
        
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