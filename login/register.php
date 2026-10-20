<?php

require_once("../common.php");

$error = "";

/*($_SERVER["REQUEST_METHOD"] === "POST")は
POSTで送信されたときだけ、この処理を実行する*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"];
    $tel = $_POST["tel"];
    $email = $_POST["email"];

    $conn = db_conn3();
    
    //id だけ取得↓↓
    $sql = "SELECT id FROM `users` WHERE email = :email";
    $stmt = $conn -> prepare($sql);
    $stmt -> bindParam(":email", $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user){
        
        //すでに登録されている場合
        $error = "このメールアドレスはすでに登録されています。";
    }else{
    // パスワードをハッシュ化はPASSを複雑にすること
    //PASSWORD_DEFAULTは、PHPが推奨するデフォルトの安全なハッシュ方式を使ってということ
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    
    
    $sql = "INSERT INTO users (name, tel, email, password)
            VALUES (:name, :tel, :email, :password)";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":tel", $tel);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $password);

    $stmt->execute();
    
    // 登録成功 → ログイン画面へ
    header("Location: login.php");
    exit;
        
        }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ORIPA SITE - 新規入会</title>

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

        <span>新規入会画面</span>

        <form action="register.php" method="POST">
            <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

                
                <input type="text" name="name"
                    placeholder="氏名" required>
            
                 <input type="tel" name="tel"
                    placeholder="電話番号" required>
            
                <input type="email" name="email"
                    placeholder="メールアドレス" required>

                <input type="password" name="password"
                    placeholder="パスワード" required>


            <button type="submit">登録</button>
            
            <p class="login-link">
                <a href= "login.php">ログインはこちら</a>
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
