<?php

session_start();

require_once("../../common.php");


// POSTか確認
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("不正なアクセスです");
}


// フォームから取得
$name = $_POST["name"];
$description = $_POST["description"];

$price = (int)$_POST["price"];
$stock = (int)$_POST["stock"];
$max_stock = (int)$_POST["max_stock"];


// 在庫チェック
if ($stock > $max_stock) {
    exit("在庫数は総在庫数以下にしてください");
}


// 画像チェック
if (
    !isset($_FILES["image"]) ||
    $_FILES["image"]["error"] !== UPLOAD_ERR_OK
) {
    exit("画像のアップロードに失敗しました");
}


// 画像情報
$image = $_FILES["image"];


// 拡張子
$extension = strtolower(
    pathinfo($image["name"], PATHINFO_EXTENSION)
);


// 許可する拡張子
$allowedExtensions = [
    "jpg",
    "jpeg",
    "png",
    "gif",
    "webp"
];


// 拡張子チェック
if (!in_array($extension, $allowedExtensions, true)) {
    exit("対応していない画像形式です");
}


// 一時保存用ファイル名
$fileName = uniqid("oripa_", true) . "." . $extension;


// 一時保存先
$uploadPath = "../../images/temp/" . $fileName;


// 画像を一時保存
if (!move_uploaded_file(
    $image["tmp_name"],
    $uploadPath
)) {
    exit("画像の保存に失敗しました");
}


// セッションに保存
$_SESSION["oripa_add"] = [
    "name" => $name,
    "description" => $description,
    "price" => $price,
    "stock" => $stock,
    "max_stock" => $max_stock,
    "image" => $fileName
];

?>


<!DOCTYPE html>
<html lang="ja">

<head>
<meta charset="UTF-8">
<title>COLLECTOR'S CLUB_商品追加確認</title>
<link href="../../css/admin.css" media="all" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

<main>

<h2>商品追加確認</h2>

<dl>
    <div class="form-item">
        <dt>商品名</dt>
        <dd>
            <?php echo htmlspecialchars($name, ENT_QUOTES, "UTF-8"); ?>
        </dd>
    </div>

    <div class="form-item">
        <dt>商品説明</dt>
        <dd>
            <?php echo nl2br(htmlspecialchars($description, ENT_QUOTES, "UTF-8")); ?>
        </dd>
    </div>

    <div class="form-item">
        <dt>価格</dt>
        <dd>
            <?php echo number_format($price); ?> ポイント
        </dd>
    </div>

    <div class="form-item">
        <dt>在庫数</dt>
        <dd>
            <?php echo $stock; ?>
        </dd>
    </div>

    <div class="form-item">
        <dt>総在庫数</dt>
        <dd>
            <?php echo $max_stock; ?>
        </dd>
    </div>

    <div class="form-item">
        <dt>画像ファイル</dt>
        <dd>
            <img src="../../images/temp/<?php 
                      echo htmlspecialchars($fileName, ENT_QUOTES, "UTF-8"); ?>"
                alt="商品画像"
                width="200">
        </dd>
    </div>

</dl>


<p>
    上記の商品を登録します。
</p>


<form method="POST"
      action="oripa_add_action.php">

    <div class="form-submit">

        <input type="submit"
               value="登録">

    </div>

</form>


<div class="form-submit">

    <input type="button"
           onclick="history.back()"
           value="戻る">

</div>


</main>


<footer>

<p>
    Copyright 2026 COLLECTOR'S CLUB.
</p>

</footer>

</body>

</html>
