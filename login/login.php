<?php

require_once("../common.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];
/*($_SERVER["REQUEST_METHOD"] === "POST")は
POSTで送信されたときだけ、この処理を実行するということ*/

    $conn = db_conn3();

    $sql = "SELECT * FROM users WHERE email = :email";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(":email", $email);
    $stmt->execute();

    // fetch()は,SQLの検索結果を1件取り出す。
    //PDO::FETCH_ASSOC これは、DBのカラム名をキーにした配列として取得する
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ユーザーが存在して、パスワードが一致するか確認
    if ($user && password_verify($password, $user["password"])) {

    session_start();
    //「$user の中に入っている id、name、point をSESSIONに入れる」
    $_SESSION["id"] = $user["id"];
    $_SESSION["name"] = $user["name"];
    $_SESSION["point"] = $user["point"];

    header("Location: mypage.php");
    exit;

    } else {

        echo "メールアドレスまたはパスワードが間違っています。";

    }
    
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ORIPA SITE - ログイン</title>

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

        <span>ログイン画面</span>

        <form action="login.php" method="POST">

            <input type="email" name="email"
                placeholder="メールアドレス" required>

            <input type="password" name="password"
                placeholder="パスワード" required>

            <button type="submit">ログイン</button>
            
            <p class="register-link">
                新規会員登録は<a href= "register.php">こちら</a>
            </p>

        </form>
        
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
