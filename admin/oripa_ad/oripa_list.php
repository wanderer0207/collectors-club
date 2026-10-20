<?php

session_start();

require_once("../../common.php");

$conn = db_conn3();


// オリパを取得
$sql = "SELECT
        id,
        name,
        description,
        price,
        stock,
        max_stock,
        image
    FROM oripa
    ORDER BY id ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute();

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>COLLECTOR'S CLUB_商品一覧</title>
    <link href="../../css/admin.css" media="all" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">

</head>

<body>

<main>
    <h2>商品一覧</h2>


<div class="form-submit">
        <a href="oripa_add.html">
            商品を追加する
        </a>

</div>


    <table>

        <tr>
            <th>ID</th>
            <th>画像</th>
            <th>商品名</th>
            <th>価格</th>
            <th>在庫</th>
            <th>操作</th>
        </tr>


        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row["id"]; ?></td>

                <td>
                    <img src="../../images/<?php echo htmlspecialchars($row["image"], ENT_QUOTES, "UTF-8");?>"
                        alt="商品画像" width="100">
                </td>

                <td>
                    <?php echo htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8"); ?>
                </td>

                <td>
                    <?php echo number_format($row["price"]); ?>ポイント
                </td>

                <td>
                    <?php echo $row["stock"]; ?>
                    /
                    <?php echo $row["max_stock"]; ?>
                </td>

                <td>
                    <a href="oripa_edit.php?id=<?php echo $row["id"]; ?>"  class="button">
                        編集
                    </a>

                    <a href="oripa_delete.php?id=<?php echo $row["id"]; ?>"  class="button">
                        削除
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

</main>


<footer>

    <p>
        Copyright 2026 COLLECTOR'S CLUB.
    </p>

</footer>

</body>

</html>
