<?php

session_start();

require_once("../../common.php");

$conn = db_conn3();


// IDを取得
if (!isset($_GET["id"])) {
    exit("商品IDがありません");
}

$id = (int)$_GET["id"];


// 商品を取得
$sql = "SELECT id, name, description, price, stock, max_stock, image
    FROM oripa
    WHERE id = :id
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ":id" => $id
]);


// 商品データ
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
    <title>COLLECTOR'S CLUB_商品編集</title>
    <link href="../../css/admin.css" media="all" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

<main>
    <h2>商品編集</h2>

    <form method="POST" action="./oripa_edit_action.php"
          enctype="multipart/form-data">
        <!-- ID -->
        <input type="hidden" name="id" value="<?php echo $row["id"]; ?>">

        <dl>
            <!-- 商品名 -->
            <div class="form-item">
                <dt>商品名</dt>
                <dd>
                    <input type="text" name="name"
                        value="<?php echo htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8"); ?>"
                        required>
                </dd>
            </div>

            <!-- 商品説明 -->
            <div class="form-item">
                <dt>商品説明</dt>
                <dd>
                    <textarea name="description" rows="6" required>
                        <?php echo htmlspecialchars($row["description"], ENT_QUOTES, "UTF-8"); ?>
                    </textarea>
                </dd>
            </div>

            <!-- 価格 -->
            <div class="form-item">
                <dt>価格</dt>
                <dd>
                    <input type="number" name="price"
                        value="<?php echo $row["price"]; ?>" required>
                </dd>
            </div>

            <!-- 在庫 -->
            <div class="form-item">
                <dt>在庫数</dt>
                <dd>
                    <input type="number" name="stock"
                        value="<?php echo $row["stock"]; ?>" required>
                </dd>
            </div>

            <!-- 総在庫 -->
            <div class="form-item">
                <dt>総在庫数</dt>
                <dd>
                    <input type="number" name="max_stock"
                        value="<?php echo $row["max_stock"]; ?>" required>
                </dd>
            </div>

            <!-- 現在の画像 -->
            <div class="form-item">
                <dt>現在の画像</dt>
                <dd>
                    <img src="../../images/<?php echo htmlspecialchars($row["image"], ENT_QUOTES, "UTF-8"); ?>"
                        alt="商品画像"
                        width="200">
                </dd>
            </div>


            <!-- 新しい画像 -->

            <div class="form-item">
                <dt>新しい画像</dt>
                <dd>
                    <input type="file" name="image" accept="image/*">
                    <p>
                        ※変更しない場合は選択しなくてOKです
                    </p>
                </dd>
            </div>

        </dl>

        <div class="form-submit">
            <input type="submit" value="更新">
        </div>

        <div class="form-submit">
            <input type="button" onclick="history.back()" value="戻る">
        </div>

    </form>
</main>

<footer>

    <p>
        Copyright 2026 COLLECTOR'S CLUB.
    </p>

</footer>


</body>

</html>
