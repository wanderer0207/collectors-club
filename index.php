<?php

session_start();

require_once("common.php");

$isLogin = isset($_SESSION["id"]);

$conn = db_conn3();


// お試しオリパ　idが最小のもの
$sql = "SELECT id, name, description, price, stock, max_stock, image
    FROM oripa
    WHERE stock > 0
    ORDER BY id ASC
    LIMIT 1";

$stmtTrial = $conn->prepare($sql);
$stmtTrial->execute();

$trialOripa = $stmtTrial->fetch(PDO::FETCH_ASSOC);

$trialPercentage = ($trialOripa["stock"] / $trialOripa["max_stock"]) * 100;



// おすすめオリパ
$sql = "SELECT id, name, description, price, stock, max_stock, image
    FROM oripa
    WHERE stock > 0
      AND id != :trial_id
    ORDER BY id ASC";

$stmt = $conn->prepare($sql);

$stmt->bindValue(":trial_id", $trialOripa["id"], PDO::PARAM_INT);

$stmt->execute();

?>



<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>COLLECTOR'S CLUB | カードを楽しもう</title>

    <link href="css/style.css" rel="stylesheet">

    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="copyrights" content="COLLECTOR'S CLUB">
    <meta name="robots" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

<header class="site-header">
    <nav class="header">

        <h1 class="site-logo">
            <a href="index.php">COLLECTOR'S CLUB</a>
        </h1>

        <ul class="log">
            <?php if ($isLogin): ?>

                <!-- ログイン中 -->
                <li><a href="./login/mypage.php">マイページ</a></li>
                <li><a href="./login/logout.php">ログアウト</a></li>

            <?php else: ?>

                <!-- ログアウト中 -->
                <li><a href="./login/register.php">会員登録</a></li>
                <li><a href="./login/login.php">ログイン</a></li>

            <?php endif; ?>
        </ul>

    </nav>
</header>


<main>

    <!-- =========================
         ヒーロー
    ========================== -->

    <section class="hero">

        <div class="hero-content">

            <p class="hero-kicker">YOUR NEXT BIG PULL STARTS HERE</p>

            <span class="hero-label">今だけのお試しオリパ</span>

            <h2>
                <?php echo htmlspecialchars($trialOripa["name"], ENT_QUOTES, "UTF-8"); ?>
            </h2>

            <p>
                <?php echo nl2br(
                    htmlspecialchars(
                        $trialOripa["description"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                ); ?>
            </p>

            <div class="hero-actions">
            <a
                href="oripa_detail.php?id=<?php echo $trialOripa["id"]; ?>"
                class="button"
            >
                ガチャ内容を見る
            </a>

            <a
                href="#"
                class="button gachaButton"
                data-price="<?php echo $trialOripa["price"]; ?>"
                data-name="<?php echo htmlspecialchars($trialOripa["name"], ENT_QUOTES, "UTF-8"); ?>"
                data-oripa="<?php echo $trialOripa["id"]; ?>"
            >
                ガチャを引く
            </a>
            </div>

            <div class="hero-stock-panel">
                <div class="hero-stock-heading">
                    <span>LIMITED STOCK</span>
                    <strong>残り<?php echo number_format($trialOripa["stock"]); ?><small> / <?php echo number_format($trialOripa["max_stock"]); ?></small></strong>
                </div>
                <div class="gauge">
                    <div class="gauge-bar" style="width: <?php echo max(0, min(100, $trialPercentage)); ?>%;"></div>
                </div>
                <p class="hero-price">1回 <strong><?php echo number_format($trialOripa["price"]); ?></strong> ポイント</p>
            </div>

        </div>

    </section>


    <!-- =========================
         おすすめ
    ========================== -->

    <section class="list" id="recommend">

        <div class="section-heading">
            <p class="section-kicker">PICK YOUR FAVORITE</p>
            <h3>今週のおすすめオリパ</h3>
            <p>気になるオリパを見つけて、さっそく運試し。</p>
        </div>

        <div class="list-content">

            
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
            $percentage = ($row["stock"] / $row["max_stock"]) * 100;?>

            <article class="card">

                <div class="card-image">
                    <img src="images/<?php echo htmlspecialchars($row["image"]); ?>"
                         alt="<?php echo htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8"); ?>のイメージ">
                    <span class="card-tag">PICK UP</span>
                </div>

                <div class="card-body">
                <h4>
                    <?php echo htmlspecialchars($row["name"]); ?>
                </h4>

                <p class="card-description">
                    <?php echo htmlspecialchars($row["description"]); ?>
                </p>

                <a href="oripa_detail.php?id=<?php echo $row["id"]; ?>"
                   class="button">
                    ガチャ内容を見る
                </a>

                <a href="#"
                   class="button gachaButton"
                   data-price="<?php echo $row["price"]; ?>"
                   data-name="<?php echo htmlspecialchars($row["name"]); ?>"
                   data-oripa="<?php echo $row["id"]; ?>">
                    ガチャを引く
                </a>

                <p class="price">
                    <strong><?php echo number_format($row["price"]); ?></strong><span>ポイント / 1回</span>
                </p>

                <div class="card-stock-label"><span>残り <?php echo number_format($row["stock"]); ?> / <?php echo number_format($row["max_stock"]); ?></span><span>在庫</span></div>
                <div class="gauge">
                    <div
                        class="gauge-bar"
                        style="width: <?php echo max(0, min(100, $percentage)); ?>%;"
                    ></div>
                </div>
                </div>
            </article>

            <?php endwhile; ?>


        </div>

    </section>


    <!-- =========================
         ガチャモーダル、ガチャを引いたときに画面に出てくる大きなウィンドウ
    ========================== -->

    <div id="gachaModal" class="gacha-modal">

        <!-- 背景 -->
        <div class="gacha-bg"></div>

        <!-- キラキラ -->
        <div class="particles"></div>

        <!-- 雷 -->
        <div class="lightning lightning1"></div>
        <div class="lightning lightning2"></div>

        <!-- 爆発 -->
        <div class="explosion-effect">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>


        <div class="gacha-box">

            <p id="gachaTitle">
                運命を決める時…
            </p>


            <div class="gacha-animation">

                <div class="gacha-card">
                    ?
                </div>

            </div>


            <p id="gachaText">
                抽選中...
            </p>


            <button id="closeGacha" class="button">
                閉じる
            </button>

        </div>

    </div>

</main>


<footer>

    <p>
        copyright &copy; 2026 COLLECTOR'S CLUB
    </p>

</footer>


<script src="js/script.js"></script>

</body>
</html>
