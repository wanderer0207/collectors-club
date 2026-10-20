<?php
session_start();

require_once("../../common.php");

$conn = db_conn3();

// POST以外は一覧へ戻す
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: list.php");
    exit;
}

// IDを取得
$id = $_POST["id"] ?? "";

if ($id === "") {
    exit("カードIDが指定されていません。");
}

if (!is_numeric($id)) {
    exit("カードIDが正しくありません。");
}

// カード情報を取得
$sql = "
    SELECT image
    FROM cards
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

$card = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$card) {
    exit("カードが見つかりません。");
}

// データベースから削除
$sql = "
    DELETE FROM cards
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

// 画像も削除
if (!empty($card["image"])) {

    $image_path = __DIR__ . "/../../images/cards/" . $card["image"];

    if (file_exists($image_path)) {
        unlink($image_path);
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>削除完了</title>
    <link rel="stylesheet" href="../../css/admin.css">
</head>

<body>

<div class="container">

    <h1>削除完了</h1>

    <p class="message">
        カードを削除しました。
    </p>

    <a href="list.php" class="button">
        カード一覧へ戻る
    </a>

</div>

</body>

</html>