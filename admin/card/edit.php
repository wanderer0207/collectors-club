<?php
session_start();

require_once("../../common.php");

$conn = db_conn3();

// IDを取得
$id = $_GET["id"] ?? "";

if ($id === "") {
    exit("カードIDが指定されていません。");
}

// カード情報を取得
$sql = "
    SELECT
        cards.id,
        cards.oripa_id,
        cards.name,
        cards.rarity,
        cards.stock,
        cards.weight,
        oripa.name AS oripa_name
    FROM cards
    INNER JOIN oripa
        ON cards.oripa_id = oripa.id
    WHERE cards.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

$card = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$card) {
    exit("カードが見つかりません。");
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>カード編集</title>
    <link rel="stylesheet" href="../../css/admin.css">
</head>

<body>

<div class="container">

    <h1>カード編集</h1>

    <form action="edit_action.php" method="post">

        <!-- カードID -->
        <input
            type="hidden"
            name="id"
            value="<?= htmlspecialchars($card["id"], ENT_QUOTES, "UTF-8") ?>">

        <!-- オリパ -->
        <div class="form-group">

            <label>オリパ</label>

            <input
                type="text"
                value="<?= htmlspecialchars($card["oripa_name"], ENT_QUOTES, "UTF-8") ?>"
                readonly>

        </div>

        <!-- カード名 -->
        <div class="form-group">

            <label>カード名</label>

            <input
                type="text"
                name="name"
                maxlength="100"
                value="<?= htmlspecialchars($card["name"], ENT_QUOTES, "UTF-8") ?>"
                required>

        </div>

        <!-- レアリティ -->
        <div class="form-group">

            <label>レアリティ</label>

            <select name="rarity" required>

                <option value="SSSR" <?= $card["rarity"] === "SSSR" ? "selected" : "" ?>>SSSR</option>
                <option value="SSR" <?= $card["rarity"] === "SSR" ? "selected" : "" ?>>SSR</option>
                <option value="SAR" <?= $card["rarity"] === "SAR" ? "selected" : "" ?>>SAR</option>
                <option value="SR" <?= $card["rarity"] === "SR" ? "selected" : "" ?>>SR</option>
                <option value="AR" <?= $card["rarity"] === "AR" ? "selected" : "" ?>>AR</option>
                <option value="R" <?= $card["rarity"] === "R" ? "selected" : "" ?>>R</option>
                <option value="N" <?= $card["rarity"] === "N" ? "selected" : "" ?>>N</option>

            </select>

        </div>

        <!-- 在庫 -->
        <div class="form-group">

            <label>在庫</label>

            <input
                type="number"
                name="stock"
                min="0"
                value="<?= htmlspecialchars($card["stock"], ENT_QUOTES, "UTF-8") ?>"
                required>

        </div>

        <!-- 重み -->
        <div class="form-group">

            <label>重み</label>

            <input
                type="number"
                name="weight"
                min="1"
                value="<?= htmlspecialchars($card["weight"], ENT_QUOTES, "UTF-8") ?>"
                required>

        </div>

        <button type="submit" class="button">
            更新する
        </button>

        <a href="list.php" class="back">
            戻る
        </a>

    </form>

</div>

</body>

</html>