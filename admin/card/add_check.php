<?php

session_start();

require_once("../../common.php");

$conn = db_conn3();

// POST以外は登録画面へ戻す
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add.php");
    exit;
}


// =========================
// 入力値を取得
// =========================

$oripa_id = $_POST["oripa_id"] ?? "";
$name     = trim($_POST["name"] ?? "");
$rarity   = $_POST["rarity"] ?? "";
$stock    = $_POST["stock"] ?? "";
$weight   = $_POST["weight"] ?? "";


// =========================
// エラーチェック
// =========================

$errors = [];


// オリパ
if ($oripa_id === "") {
    $errors[] = "オリパを選択してください。";
}


// カード名
if ($name === "") {
    $errors[] = "カード名を入力してください。";
}


// レアリティ
if ($rarity === "") {
    $errors[] = "レアリティを選択してください。";
}


// 在庫
if ($stock === "" || !is_numeric($stock) || $stock < 1) {
    $errors[] = "在庫は1以上で入力してください。";
}


// 重み
if ($weight === "" || !is_numeric($weight) || $weight < 1) {
    $errors[] = "重みは1以上で入力してください。";
}


// =========================
// 画像チェック
// =========================

if (
    !isset($_FILES["image"]) ||
    $_FILES["image"]["error"] !== UPLOAD_ERR_OK
) {
    $errors[] = "カード画像を選択してください。";
}


// =========================
// エラーがある場合
// =========================

if (!empty($errors)) {
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>入力エラー</title>

    <link rel="stylesheet" href="../../css/admin.css">
</head>

<body>

<div class="container">

    <h1>入力エラー</h1>

    <div class="message">

        <?php foreach ($errors as $error): ?>

            <p>
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </p>

        <?php endforeach; ?>

    </div>

    <a href="add.php" class="button">
        カード登録画面へ戻る
    </a>

</div>

</body>

</html>

<?php
    exit;
}


// =========================
// オリパを確認
// =========================

$sql = "
    SELECT id, name
    FROM oripa
    WHERE id = :id
";

$stmt = $conn->prepare($sql);

$stmt->bindValue(
    ":id",
    $oripa_id,
    PDO::PARAM_INT
);

$stmt->execute();

$oripa = $stmt->fetch(PDO::FETCH_ASSOC);


// オリパが存在しない
if (!$oripa) {
    die("指定されたオリパが存在しません。");
}


// =========================
// 画像情報
// =========================

$image_tmp_name = $_FILES["image"]["tmp_name"];

$image_original_name = $_FILES["image"]["name"];


// 拡張子
$extension = strtolower(
    pathinfo($image_original_name, PATHINFO_EXTENSION)
);


// 許可する拡張子
$allowed_extensions = [
    "jpg",
    "jpeg",
    "png",
    "gif",
    "webp"
];


// 拡張子チェック
if (!in_array($extension, $allowed_extensions, true)) {

    die("対応していない画像形式です。");

}


// =========================
// 一時保存フォルダ
// =========================

$temp_dir = __DIR__ . "/../../images/cards/temp/";


// フォルダがなければ作成
if (!is_dir($temp_dir)) {

    if (!mkdir($temp_dir, 0777, true)) {

        die("一時保存フォルダを作成できませんでした。");

    }

}


// =========================
// 一時ファイル名
// =========================

$temp_image_name =
    uniqid("temp_card_", true)
    . "."
    . $extension;


$temp_image_path =
    $temp_dir
    . $temp_image_name;


// =========================
// 一時保存
// =========================

if (!move_uploaded_file(
    $image_tmp_name,
    $temp_image_path
)) {

    die("画像の一時保存に失敗しました。");

}

?>

<!DOCTYPE html>
<html lang="ja">

<head>

    <meta charset="UTF-8">

    <title>カード登録確認</title>

    <link rel="stylesheet" href="../../css/admin.css">

</head>

<body>

<div class="container">

    <h1>カード登録確認</h1>


    <!-- =========================
         確認内容
    ========================== -->

    <div class="message">

        <p>
            <strong>オリパ</strong><br>
            <?= htmlspecialchars(
                $oripa["name"],
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </p>


        <p>
            <strong>カード名</strong><br>
            <?= htmlspecialchars(
                $name,
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </p>


        <p>
            <strong>レアリティ</strong><br>
            <?= htmlspecialchars(
                $rarity,
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </p>


        <p>
            <strong>在庫</strong><br>
            <?= htmlspecialchars(
                $stock,
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </p>


        <p>
            <strong>重み</strong><br>
            <?= htmlspecialchars(
                $weight,
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </p>


        <p>
            <strong>画像</strong><br>

            <?= htmlspecialchars(
                $image_original_name,
                ENT_QUOTES,
                "UTF-8"
            ) ?>

        </p>

    </div>


    <!-- =========================
         登録フォーム
    ========================== -->

    <form
        action="add_action.php"
        method="post"
    >

        <input
            type="hidden"
            name="oripa_id"
            value="<?= htmlspecialchars(
                $oripa_id,
                ENT_QUOTES,
                "UTF-8"
            ) ?>"
        >


        <input
            type="hidden"
            name="name"
            value="<?= htmlspecialchars(
                $name,
                ENT_QUOTES,
                "UTF-8"
            ) ?>"
        >


        <input
            type="hidden"
            name="rarity"
            value="<?= htmlspecialchars(
                $rarity,
                ENT_QUOTES,
                "UTF-8"
            ) ?>"
        >


        <input
            type="hidden"
            name="stock"
            value="<?= htmlspecialchars(
                $stock,
                ENT_QUOTES,
                "UTF-8"
            ) ?>"
        >


        <input
            type="hidden"
            name="weight"
            value="<?= htmlspecialchars(
                $weight,
                ENT_QUOTES,
                "UTF-8"
            ) ?>"
        >


        <!-- 一時保存した画像 -->
        <input
            type="hidden"
            name="temp_image"
            value="<?= htmlspecialchars(
                $temp_image_name,
                ENT_QUOTES,
                "UTF-8"
            ) ?>"
        >


        <button
            type="submit"
            class="button"
        >
            この内容で登録する
        </button>


        <a
            href="add.php"
            class="back"
        >
            戻る
        </a>

    </form>

</div>

</body>

</html>