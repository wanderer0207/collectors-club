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
        cards.name,
        cards.rarity,
        cards.stock,
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
    <title>カード削除</title>
    <link rel="stylesheet" href="../../css/admin.css">
</head>

<body>

<div class="container">

    <h1>カード削除</h1>

    <p>
        以下のカードを削除します。
    </p>

    <table>

        <tr>
            <th>ID</th>
            <td>
                <?= htmlspecialchars($card["id"], ENT_QUOTES, "UTF-8") ?>
            </td>
        </tr>

        <tr>
            <th>オリパ</th>
            <td>
                <?= htmlspecialchars($card["oripa_name"], ENT_QUOTES, "UTF-8") ?>
            </td>
        </tr>

        <tr>
            <th>カード名</th>
            <td>
                <?= htmlspecialchars($card["name"], ENT_QUOTES, "UTF-8") ?>
            </td>
        </tr>

        <tr>
            <th>レアリティ</th>
            <td>
                <?= htmlspecialchars($card["rarity"], ENT_QUOTES, "UTF-8") ?>
            </td>
        </tr>

        <tr>
            <th>在庫</th>
            <td>
                <?= htmlspecialchars($card["stock"], ENT_QUOTES, "UTF-8") ?>
            </td>
        </tr>

    </table>

    <p class="message">
        本当にこのカードを削除しますか？
    </p>

    <form action="delete_action.php" method="post">

        <input
            type="hidden"
            name="id"
            value="<?= htmlspecialchars($card["id"], ENT_QUOTES, "UTF-8") ?>">

        <button type="submit" class="button">
            削除する
        </button>

        <a href="list.php" class="back">
            キャンセル
        </a>

    </form>

</div>

</body>

</html>