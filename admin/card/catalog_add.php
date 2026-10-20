```php
<?php
session_start();
require_once("../../common.php");

$conn = db_conn3();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: catalog_search.php");
    exit;
}

// 入力値を確認
$catalogId = filter_input(INPUT_POST, "catalog_id", FILTER_VALIDATE_INT);
$oripaId = filter_input(INPUT_POST, "oripa_id", FILTER_VALIDATE_INT);
$stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);
$weight = filter_input(INPUT_POST, "weight", FILTER_VALIDATE_INT);

if (
    !$catalogId || !$oripaId ||
    !$stock || !$weight ||
    $stock < 1 || $stock > 100000 ||
    $weight < 1 || $weight > 1000000
) {
    exit("入力内容が正しくありません。");
}

// 登録先のオリパを確認
$stmt = $conn->prepare("
    SELECT id, name
    FROM oripa
    WHERE id = :id AND is_active = 1
");
$stmt->execute([":id" => $oripaId]);
$oripa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$oripa) {
    exit("登録先のオリパが見つからないか、無効になっています。");
}

// カード図鑑からカード情報を取得
$stmt = $conn->prepare("
    SELECT id, name, rarity, image_filename
    FROM card_catalog
    WHERE id = :id
");
$stmt->execute([":id" => $catalogId]);
$card = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$card) {
    exit("カード図鑑にカードが見つかりません。");
}

// 画像がない場合は空文字のまま登録する
$imageName = "";
$destination = null;

$sourceName = trim((string)($card["image_filename"] ?? ""));

// 画像が指定されている場合だけ、画像の確認とコピーを行う
if ($sourceName !== "") {

    // ファイル名の安全性を確認
    if (
        basename($sourceName) !== $sourceName ||
        !preg_match('/\.(jpg|jpeg|png|webp)$/i', $sourceName)
    ) {
        exit("画像ファイル名が正しくありません。");
    }

    // 元画像のフォルダを確認
    $catalogDir = realpath(
        __DIR__ . "/../../images/cards/catalog"
    );

    if ($catalogDir === false) {
        exit("カード画像フォルダが見つかりません。");
    }

    $sourcePath = $catalogDir
        . DIRECTORY_SEPARATOR . $sourceName;

    if (!is_file($sourcePath)) {
        exit("カード画像ファイルが見つかりません。");
    }

    // 画像の実際の形式を確認
    $imageInfo = @getimagesize($sourcePath);

    $mimeExtensions = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (
        !$imageInfo ||
        !isset($mimeExtensions[$imageInfo["mime"]])
    ) {
        exit("対応していない画像形式です。");
    }

    // 保存先フォルダ
    $imageDir = __DIR__ . "/../../images/cards/";

    if (
        !is_dir($imageDir) &&
        !mkdir($imageDir, 0755, true) &&
        !is_dir($imageDir)
    ) {
        exit("画像保存フォルダを作成できませんでした。");
    }

    // 保存用のランダムなファイル名を作成
    $extension = $mimeExtensions[$imageInfo["mime"]];
    $imageName = bin2hex(random_bytes(16)) . "." . $extension;
    $destination = $imageDir . $imageName;

    // 元画像をコピー
    if (!copy($sourcePath, $destination)) {

        exit("画像のコピーに失敗しました。");
    }
}

// カード情報をオリパに登録
try {
    $stmt = $conn->prepare("
        INSERT INTO cards
            (oripa_id, name, rarity, image, stock, weight)
        VALUES
            (:oripa_id, :name, :rarity, :image, :stock, :weight)
    ");

    $stmt->execute([
        ":oripa_id" => $oripaId,
        ":name" => $card["name"],
        ":rarity" => $card["rarity"],
        ":image" => $imageName,
        ":stock" => $stock,
        ":weight" => $weight
    ]);

} catch (Throwable $e) {
    if (
        $destination !== null &&
        is_file($destination)
    ) {
        @unlink($destination);
    }

    error_log("catalog_add.php: " . $e->getMessage());

    exit(
        "オリパへの登録に失敗しました。" .
        "DBの設定とテーブルを確認してください。"
    );
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>カード登録完了</title>
<link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
<div class="container">
    <h1>カード登録完了</h1>

    <p class="message">
        「<?= h($card["name"]) ?>」を
        「<?= h($oripa["name"]) ?>」に登録しました。
    </p>

    <p>
        画像：<?= $imageName !== "" ? "登録済み" : "画像なし" ?>
    </p>

    <p>在庫：<?= (int)$stock ?></p>
    <p>抽選ウェイト：<?= (int)$weight ?></p>

    <p>
        <a class="button" href="catalog_search.php">
            カード検索に戻る
        </a>
    </p>

    <p>
        <a class="back" href="list.php">
            既存のカード一覧へ
        </a>
    </p>
</div>
</body>
</html>

