<?php

session_start();

require_once("../../common.php");

$conn = db_conn3();


// POSTか確認
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("不正なアクセスです");
}


// フォームから取得
$id = (int)$_POST["id"];

$name = $_POST["name"];
$description = $_POST["description"];

$price = (int)$_POST["price"];
$stock = (int)$_POST["stock"];
$max_stock = (int)$_POST["max_stock"];


// 在庫チェック
if ($stock > $max_stock) {
    exit("在庫数は総在庫数以下にしてください");
}


// 現在の商品情報を取得
$sql = "SELECT image FROM oripa WHERE id = :id";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$row) {
    exit("商品が見つかりません");
}


// 現在の画像
$fileName = $row["image"];


// =========================
// 新しい画像がある場合
// =========================

if (
    isset($_FILES["image"]) &&
    $_FILES["image"]["error"] === UPLOAD_ERR_OK
) {

    $image = $_FILES["image"];


    // 拡張子
    $extension = strtolower(
        pathinfo($image["name"], PATHINFO_EXTENSION)
    );


    // 許可する画像
    $allowedExtensions = [
        "jpg",
        "jpeg",
        "png",
        "gif",
        "webp"
    ];


    // 拡張子チェック
    if (!in_array($extension, $allowedExtensions, true)) {
        exit("対応していない画像形式です");
    }


    // 新しいファイル名
    $newFileName = uniqid("oripa_", true) . "." . $extension;


    // 保存先
    $uploadPath = "../../images/" . $newFileName;


    // 画像を保存
    if (!move_uploaded_file(
        $image["tmp_name"],
        $uploadPath
    )) {
        exit("画像の保存に失敗しました");
    }


    // 古い画像を削除
    if (
        !empty($fileName) &&
        file_exists("../../images/" . $fileName)
    ) {

        unlink("../../images/" . $fileName);

    }


    // 新しい画像名に変更
    $fileName = $newFileName;
}


// =========================
// DB更新
// =========================

$sql = "
    UPDATE oripa
    SET
        name = :name,
        description = :description,
        price = :price,
        stock = :stock,
        max_stock = :max_stock,
        image = :image
    WHERE id = :id
";


$stmt = $conn->prepare($sql);

$stmt->execute([
    ":name" => $name,
    ":description" => $description,
    ":price" => $price,
    ":stock" => $stock,
    ":max_stock" => $max_stock,
    ":image" => $fileName,
    ":id" => $id
]);


// 商品一覧へ
header("Location: oripa_list.php");
exit;

?>
