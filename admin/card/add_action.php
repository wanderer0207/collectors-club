<?php

session_start();

require_once("../../common.php");

$conn = db_conn3();


// =========================
// POSTチェック
// =========================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: add.php");
    exit;

}


// =========================
// 入力値
// =========================

$oripa_id = $_POST["oripa_id"] ?? "";
$name     = trim($_POST["name"] ?? "");
$rarity   = $_POST["rarity"] ?? "";
$stock    = $_POST["stock"] ?? "";
$weight   = $_POST["weight"] ?? "";

$temp_image = $_POST["temp_image"] ?? "";


// =========================
// 入力チェック
// =========================

if (
    $oripa_id === "" ||
    $name === "" ||
    $rarity === "" ||
    $stock === "" ||
    $weight === "" ||
    $temp_image === ""
) {

    die("必要な情報がありません。");

}


// =========================
// 一時画像
// =========================

$temp_dir =
    __DIR__
    . "/../../images/cards/temp/";


$temp_image_path =
    $temp_dir
    . $temp_image;


// 一時画像が存在するか
if (!file_exists($temp_image_path)) {

    die("一時保存された画像が見つかりません。");

}


// =========================
// 正式保存先
// =========================

$image_dir =
    __DIR__
    . "/../../images/cards/";


// フォルダがなければ作成
if (!is_dir($image_dir)) {

    if (!mkdir($image_dir, 0777, true)) {

        die("画像フォルダを作成できませんでした。");

    }

}


// =========================
// 正式ファイル名
// =========================

$extension =
    strtolower(
        pathinfo($temp_image, PATHINFO_EXTENSION)
    );


$image_name =
    uniqid("card_", true)
    . "."
    . $extension;


$image_path =
    $image_dir
    . $image_name;


// =========================
// 画像を正式保存
// =========================

if (!rename(
    $temp_image_path,
    $image_path
)) {

    die("画像の保存に失敗しました。");

}


// =========================
// DB登録
// =========================

try {

    $sql = "
        INSERT INTO cards
        (
            oripa_id,
            name,
            rarity,
            image,
            stock,
            weight
        )
        VALUES
        (
            :oripa_id,
            :name,
            :rarity,
            :image,
            :stock,
            :weight
        )
    ";


    $stmt = $conn->prepare($sql);


    $stmt->bindValue(
        ":oripa_id",
        $oripa_id,
        PDO::PARAM_INT
    );


    $stmt->bindValue(
        ":name",
        $name,
        PDO::PARAM_STR
    );


    $stmt->bindValue(
        ":rarity",
        $rarity,
        PDO::PARAM_STR
    );


    $stmt->bindValue(
        ":image",
        $image_name,
        PDO::PARAM_STR
    );


    $stmt->bindValue(
        ":stock",
        $stock,
        PDO::PARAM_INT
    );


    $stmt->bindValue(
        ":weight",
        $weight,
        PDO::PARAM_INT
    );


    $stmt->execute();


} catch (PDOException $e) {

    // DB登録に失敗したら画像を削除
    if (file_exists($image_path)) {

        unlink($image_path);

    }

    die("カードの登録に失敗しました。");

}

?>

<!DOCTYPE html>
<html lang="ja">

<head>

    <meta charset="UTF-8">

    <title>カード登録完了</title>

    <link rel="stylesheet" href="../../css/admin.css">

</head>

<body>

<div class="container">

    <h1>カード登録完了</h1>


    <div class="message">

        カードを登録しました。

    </div>


    <p>
        <strong>カード名：</strong>

        <?= htmlspecialchars(
            $name,
            ENT_QUOTES,
            "UTF-8"
        ) ?>
    </p>


    <p>
        <strong>レアリティ：</strong>

        <?= htmlspecialchars(
            $rarity,
            ENT_QUOTES,
            "UTF-8"
        ) ?>
    </p>


    <p>
        <strong>在庫：</strong>

        <?= htmlspecialchars(
            $stock,
            ENT_QUOTES,
            "UTF-8"
        ) ?>
    </p>


    <p>
        <strong>重み：</strong>

        <?= htmlspecialchars(
            $weight,
            ENT_QUOTES,
            "UTF-8"
        ) ?>
    </p>


    <br>


    <a
        href="list.php"
        class="button"
    >
        カード一覧へ
    </a>


    <a
        href="add.php"
        class="back"
    >
        続けて登録する
    </a>

</div>

</body>

</html>