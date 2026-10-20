<?php

session_start();

require_once("../../common.php");

$conn = db_conn3();

// カード一覧を取得
$sql = "
    SELECT
        cards.id,
        cards.oripa_id,
        cards.name,
        cards.rarity,
        cards.image,
        cards.stock,
        cards.weight,
        oripa.name AS oripa_name
    FROM cards
    INNER JOIN oripa
        ON cards.oripa_id = oripa.id
    ORDER BY cards.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();

$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">

<head>

    <meta charset="UTF-8">

    <title>カード一覧</title>

    <link rel="stylesheet" href="../../css/admin.css">

</head>

<body>

<div class="container">

    <h1>カード一覧</h1>

    <a href="add.php" class="button">
        ＋ カードを登録
    </a>

    <br><br>

    <table>

        <tr>
            <th>ID</th>
            <th>オリパ</th>
            <th>画像</th>
            <th>カード名</th>
            <th>レアリティ</th>
            <th>在庫</th>
            <th>重み</th>
            <th>操作</th>
        </tr>


        <?php foreach ($cards as $card): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($card["id"], ENT_QUOTES, "UTF-8") ?>
                </td>

                <td>
                    <?= htmlspecialchars($card["oripa_name"], ENT_QUOTES,"UTF-8") ?>
                </td>

                <td>
                    <?php if (!empty($card["image"])): ?>

                        <img src="../../images/cards/<?
                                  = htmlspecialchars($card["image"], ENT_QUOTES, "UTF-8") ?>"
                            class="card-image"
                            alt="カード画像"
                            width="80">

                    <?php else: ?>

                        画像なし

                    <?php endif; ?>

                </td>

                <td>
                    <?= htmlspecialchars($card["name"], ENT_QUOTES, "UTF-8") ?>
                </td>

                <td>
                    <?= htmlspecialchars($card["rarity"], ENT_QUOTES, "UTF-8") ?>
                </td>

                <td>
                    <?= htmlspecialchars($card["stock"], ENT_QUOTES, "UTF-8") ?>
                </td>

                <td>
                    <?= htmlspecialchars($card["weight"], ENT_QUOTES, "UTF-8") ?>
                </td>
                
                <td>
                    <a href="edit.php?id=<?= $card["id"] ?>" class="button">
                        編集
                    </a>
                    
                    <a href="delete.php?id=<?= $card["id"] ?>" class="button">
                        削除
                    </a>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>

</body>

</html>