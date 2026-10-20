<?php

session_start();

require_once("../../common.php");

$conn = db_conn3();


// ID確認
if (!isset($_GET["id"])) {
    exit("商品IDがありません");
}

$id = (int)$_GET["id"];


// 商品を取得
$sql = "SELECT id, name, description, price, stock, max_stock, image
    FROM oripa
    WHERE id = :id";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ":id" => $id
]);


// 商品情報
$row = $stmt->fetch(PDO::FETCH_ASSOC);


// 商品が存在しない場合
if (!$row) {
    exit("商品が見つかりません");
}

?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>COLLECTOR'S CLUB_商品削除</title>
    <link href="../../css/admin.css" media="all" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

<main>
    <h2>商品削除確認</h2>
    <dl>
        <!-- 商品名 -->
        <div class="form-item">
            <dt>商品名</dt>
            <dd>
                <?php echo htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8"); ?>
            </dd>
        </div>

        <!-- 商品説明 -->
        <div class="form-item">
            <dt>商品説明</dt>
            <dd>
                <?php echo nl2br(htmlspecialchars($row["description"], ENT_QUOTES, "UTF-8")); ?>
            </dd>
        </div>

        <!-- 価格 -->
        <div class="form-item">
            <dt>価格</dt>
            <dd>
                <?php echo number_format($row["price"]); ?>
                ポイント
            </dd>
        </div>

        <!-- 在庫 -->
        <div class="form-item">
            <dt>在庫</dt>
            <dd>
                <?php echo $row["stock"]; ?>
                /
                <?php echo $row["max_stock"]; ?>
            </dd>
        </div>

        <!-- 商品画像 -->
        <div class="form-item">
            <dt>商品画像</dt>
            <dd>
                <img src="../../images/<?php echo htmlspecialchars($row["image"], ENT_QUOTES, "UTF-8"); ?>"
                    alt="商品画像"
                    width="200">
            </dd>
        </div>
    </dl>

    <p>
        この商品を削除しますか？
    </p>

    <!-- 削除 -->

    <form method="POST" action="./oripa_delete_action.php">

        <input type="hidden" name="id" value="<?php echo $row["id"]; ?>">

        <div class="form-submit">
            <input type="submit" value="削除する">
        </div>

    </form>


    <!-- 戻る -->

    <div class="form-submit">
        <input type="button" onclick="history.back()" value="戻る">
    </div>

</main>


<footer>

    <p>
        Copyright 2026 COLLECTOR'S CLUB.
    </p>

</footer>


</body>

</html>
