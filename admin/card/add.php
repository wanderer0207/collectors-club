<?php

session_start();

require_once("../../common.php");

$conn = db_conn3();


// 登録可能なオリパを取得
$sql = "
    SELECT id, name
    FROM oripa
    WHERE is_active = 1
    ORDER BY id ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute();

$oripas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">

<head>

    <meta charset="UTF-8">

    <title>カード登録</title>

    <link rel="stylesheet" href="../../css/admin.css">

</head>

<body>

<div class="container">

    <h1>カード登録</h1>


    <form
        action="add_check.php"
        method="post"
        enctype="multipart/form-data"
    >


        <!-- オリパ -->

        <div class="form-group">

            <label>オリパ</label>

            <select
                name="oripa_id"
                required
            >

                <option value="">
                    選択してください
                </option>


                <?php foreach ($oripas as $oripa): ?>

                    <option
                        value="<?= htmlspecialchars(
                            $oripa["id"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                    >

                        <?= htmlspecialchars(
                            $oripa["name"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- カード名 -->

        <div class="form-group">

            <label>カード名</label>

            <input
                type="text"
                name="name"
                maxlength="100"
                required
            >

        </div>


        <!-- レアリティ -->

        <div class="form-group">

            <label>レアリティ</label>

            <select
                name="rarity"
                required
            >

                <option value="">
                    選択してください
                </option>

                <option value="SSSR">SSSR</option>
                <option value="SSR">SSR</option>
                <option value="SAR">SAR</option>
                <option value="SR">SR</option>
                <option value="AR">AR</option>
                <option value="R">R</option>
                <option value="N">N</option>

            </select>

        </div>


        <!-- 画像 -->

        <div class="form-group">

            <label>カード画像</label>

            <input
                type="file"
                name="image"
                accept="image/jpeg,image/png,image/gif,image/webp"
                required
            >

        </div>


        <!-- 在庫 -->

        <div class="form-group">

            <label>在庫</label>

            <input
                type="number"
                name="stock"
                min="1"
                required
            >

        </div>


        <!-- 重み -->

        <div class="form-group">

            <label>重み</label>

            <input
                type="number"
                name="weight"
                min="1"
                required
            >

        </div>


        <button
            type="submit"
            class="button"
        >
            確認画面へ
        </button>


        <a
            href="list.php"
            class="back"
        >
            戻る
        </a>


    </form>

</div>

</body>

</html>