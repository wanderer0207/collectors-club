
<?php
session_start();
require_once("../../common.php");

$conn = db_conn3();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$q = trim($_GET["q"] ?? "");

$sql = "
    SELECT
        id, card_number, name, rarity,
        set_code, set_name, image_filename
    FROM card_catalog
";

$params = [];

if ($q !== "") {
    $sql .= "
        WHERE name LIKE :name
           OR card_number LIKE :number
           OR set_name LIKE :set_name
           OR set_code LIKE :set_code
    ";

    $keyword = "%" . $q . "%";
    $params = [
        ":name" => $keyword,
        ":number" => $keyword,
        ":set_name" => $keyword,
        ":set_code" => $keyword
    ];
}

$sql .= " ORDER BY name ASC, card_number ASC LIMIT 100";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$catalog = $stmt->fetchAll(PDO::FETCH_ASSOC);

$oripaStmt = $conn->query("
    SELECT id, name
    FROM oripa
    WHERE is_active = 1
    ORDER BY id ASC
");
$oripas = $oripaStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>カード図鑑・検索</title>
<link rel="stylesheet" href="../../css/admin.css">
<style>
.catalog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
}
.catalog-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 14px;
    min-width: 0;
}
.catalog-item img {
    display: block;
    width: 100%;
    max-height: 260px;
    object-fit: contain;
    margin-bottom: 10px;
}
.catalog-item input,
.catalog-item select {
    max-width: 100%;
    box-sizing: border-box;
}
</style>
</head>
<body>
<div class="container">
    <h1>カード図鑑・検索</h1>

    <form method="get">
        <div class="form-group">
            <label for="q">カード名・カード番号・収録商品</label>
            <input
                type="search"
                name="q"
                id="q"
                value="<?= h($q) ?>"
                maxlength="100"
            >
        </div>
        <button class="button" type="submit">検索</button>
    </form>

    <p>検索結果：<?= count($catalog) ?>件（最大100件）</p>

    <?php if (!$oripas): ?>
        <p class="message">登録可能なオリパがありません。</p>
    <?php endif; ?>

    <div class="catalog-grid">
    <?php foreach ($catalog as $card): ?>
        <div class="catalog-item">

            
<?php if (trim((string)$card["image_filename"]) !== ""): ?>
    <?php
    $imagePath = "../../images/cards/catalog/"
        . rawurlencode($card["image_filename"]);
    ?>
    <img
        src="<?= h($imagePath) ?>"
        alt="<?= h($card["name"]) ?>"
        loading="lazy"
    >
<?php else: ?>
    <div class="no-card-image" role="img"
         aria-label="画像未登録">
        画像未登録
    </div>
<?php endif; ?>

            
            <h2><?= h($card["name"]) ?></h2>
            <p>カード番号：<?= h($card["card_number"]) ?></p>
            <p>レアリティ：<?= h($card["rarity"]) ?></p>
            <p>収録商品：<?= h($card["set_name"]) ?></p>

            <?php if ($oripas): ?>
            <form method="post" action="catalog_add.php">
                <input
                    type="hidden"
                    name="catalog_id"
                    value="<?= (int)$card["id"] ?>"
                >

                <div class="form-group">
                    <label>登録先オリパ</label>
                    <select name="oripa_id" required>
                        <?php foreach ($oripas as $oripa): ?>
                            <option value="<?= (int)$oripa["id"] ?>">
                                <?= h($oripa["name"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>在庫</label>
                    <input
                        type="number"
                        name="stock"
                        min="1"
                        max="100000"
                        value="1"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>抽選ウェイト</label>
                    <input
                        type="number"
                        name="weight"
                        min="1"
                        max="1000000"
                        value="1"
                        required
                    >
                </div>

                <button class="button" type="submit">
                    このカードをオリパに登録
                </button>
            </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>

    <?php if (!$catalog): ?>
        <p class="message">
            該当するカードがありません。先にCSVでカード情報を登録してください。
        </p>
    <?php endif; ?>

    <p><a href="catalog_import.php">CSV一括登録へ</a></p>
    <p><a href="add.php">従来の手動カード登録へ</a></p>
</div>
</body>
</html>
