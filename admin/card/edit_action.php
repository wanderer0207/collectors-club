<?php
session_start();

require_once("../../common.php");

$conn = db_conn3();

// POST以外は編集画面へ戻す
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: edit.php");
    exit;
}

// フォームの値を取得
$id = $_POST["id"] ?? "";
$name = trim($_POST["name"] ?? "");
$rarity = $_POST["rarity"] ?? "";
$stock = $_POST["stock"] ?? "";
$weight = $_POST["weight"] ?? "";

// 入力チェック
if (
    $id === "" ||
    $name === "" ||
    $rarity === "" ||
    $stock === "" ||
    $weight === ""
) {
    exit("入力されていない項目があります。");
}

// 数値チェック
if (!is_numeric($id) || !is_numeric($stock) || !is_numeric($weight)) {
    exit("数値が正しくありません。");
}

if ($stock < 0) {
    exit("在庫は0以上にしてください。");
}

if ($weight < 1) {
    exit("重みは1以上にしてください。");
}

// 更新
$sql = "
    UPDATE cards
    SET
        name = ?,
        rarity = ?,
        stock = ?,
        weight = ?
    WHERE id = ?
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    $name,
    $rarity,
    $stock,
    $weight,
    $id
]);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>カード更新完了</title>
    <link rel="stylesheet" href="../../css/admin.css">
</head>

<body>

<div class="container">

    <h1>更新完了</h1>

    <p class="message">
        カード情報を更新しました。
    </p>

    <a href="list.php" class="button">
        カード一覧へ戻る
    </a>

</div>

</body>

</html>