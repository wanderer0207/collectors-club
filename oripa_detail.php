<?php
session_start();

require_once("common.php");

$conn = db_conn3();

// オリパIDを取得
$id = $_GET["id"] ?? "";

if ($id === "" || !is_numeric($id)) {
    exit("オリパIDが正しくありません。");
}


// ==============================
// オリパ情報を取得
// ==============================

$sql = "
    SELECT
        id,
        name,
        description,
        price,
        stock,
        max_stock,
        image,
        is_active
    FROM oripa
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

$oripa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$oripa) {
    exit("オリパが見つかりません。");
}

// 非公開のオリパは表示しない
if ($oripa["is_active"] != 1) {
    exit("このオリパは現在公開されていません。");
}


// ==============================
// カード情報を取得
// ==============================

$sql = "
    SELECT
        id,
        name,
        rarity,
        image,
        stock,
        weight
    FROM cards
    WHERE oripa_id = ?
    ORDER BY weight DESC, id ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars($oripa["name"], ENT_QUOTES, "UTF-8") ?>
    </title>
    <link rel="stylesheet" href="./css/style.css">

</head>

<body>


<!-- ==============================
     ヘッダー
============================== -->

<header>

    <div class="header-inner">

        <a href="index.php" class="logo">
            COLLECTOR'S CLUB
        </a>

        <nav>
            <a href="index.php">ホーム</a>

            <a href="./login/mypage.php">マイページ</a>
        </nav>

    </div>

</header>


<!-- ==============================
     メイン
============================== -->

<main class="oripa-detail">


    <!-- ==============================
         オリパ情報
    =============================== -->

    <section class="oripa-main">


        <!-- オリパ画像 -->

        <div class="oripa-image">

            <?php if (!empty($oripa["image"])): ?>

                <img src="./images/<?= htmlspecialchars($oripa["image"], ENT_QUOTES, "UTF-8") ?>"
                    alt="<?= htmlspecialchars($oripa["name"], ENT_QUOTES, "UTF-8") ?>">

            <?php else: ?>

                <div class="no-image">
                    画像なし
                </div>

            <?php endif; ?>

        </div>


        <!-- オリパ情報 -->

        <div class="oripa-info">

            <p class="oripa-label">
                オリパ
            </p>

            <h1>
                <?= htmlspecialchars($oripa["name"], ENT_QUOTES, "UTF-8") ?>
            </h1>


            <?php if (!empty($oripa["description"])): ?>

                <p class="description">
                    <?= nl2br(htmlspecialchars($oripa["description"], ENT_QUOTES, "UTF-8")) ?>
                </p>

            <?php endif; ?>


            <div class="price">

                <?= number_format($oripa["price"]) ?>

                <span>pt</span>

            </div>


            <div class="stock">

                残り

                <strong>
                    <?= number_format($oripa["stock"]) ?>
                </strong>

                口

            </div>


            <?php if ($oripa["stock"] > 0): ?>

                <a
                    href="gacha.php?id=<?= htmlspecialchars($oripa["id"], ENT_QUOTES, "UTF-8") ?>"
                    class="gacha-button">

                    このオリパを引く

                </a>

            <?php else: ?>

                <div class="sold-out">
                    SOLD OUT
                </div>

            <?php endif; ?>

        </div>

    </section>



    <!-- ==============================
         カードラインナップ
    =============================== -->

    <section class="card-section">

        <h2>
            カードラインナップ
        </h2>

        <p class="section-description">
            このオリパから排出されるカード
        </p>


        <?php if (empty($cards)): ?>

            <p class="no-cards">
                現在、登録されているカードはありません。
            </p>

        <?php else: ?>

            <div class="card-grid">


                <?php foreach ($cards as $card): ?>

                    <div class="card-item">


                        <!-- カード画像 -->

                        <div class="card-image">

                            <?php if (!empty($card["image"])): ?>

                                <img
                                    src="images/cards/<?= htmlspecialchars($card["image"], ENT_QUOTES, "UTF-8") ?>"
                                    alt="<?= htmlspecialchars($card["name"], ENT_QUOTES, "UTF-8") ?>">

                            <?php else: ?>

                                <div class="no-card-image">
                                    画像なし
                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- レアリティ -->

                        <div class="rarity">

                            <?= htmlspecialchars($card["rarity"], ENT_QUOTES, "UTF-8") ?>

                        </div>


                        <!-- カード名 -->

                        <h3>

                            <?= htmlspecialchars($card["name"], ENT_QUOTES, "UTF-8") ?>

                        </h3>


                    </div>

                <?php endforeach; ?>


            </div>

        <?php endif; ?>

    </section>



    <!-- ==============================
         オリパ詳細
    =============================== -->

    <section class="description-section">

        <h2>
            オリパ詳細
        </h2>

        <div class="description-box">

            <?php if (!empty($oripa["description"])): ?>

                <?= nl2br(htmlspecialchars($oripa["description"], ENT_QUOTES, "UTF-8")) ?>

            <?php else: ?>

                このオリパの詳細情報はありません。

            <?php endif; ?>

        </div>

    </section>


</main>


<!-- ==============================
     フッター
============================== -->

<footer>

    <p>
        © COLLECTOR'S CLUB
    </p>

</footer>


</body>

</html>