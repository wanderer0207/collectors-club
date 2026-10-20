<?php

session_start();

require_once("../../common.php");

$conn = db_conn3();


// セッション確認
if (!isset($_SESSION["oripa_add"])) {
    exit("不正なアクセスです");
}


// セッションから取得
$data = $_SESSION["oripa_add"];

$name = $data["name"];
$description = $data["description"];
$price = $data["price"];
$stock = $data["stock"];
$max_stock = $data["max_stock"];
$fileName = $data["image"];


// 一時画像
$tempPath = "../../images/temp/" . $fileName;


// 本番画像
$uploadPath = "../../images/" . $fileName;


// 画像を正式な場所へ移動
if (!rename($tempPath, $uploadPath)) {
    exit("画像の保存に失敗しました");
}


// DB登録
$sql = "
    INSERT INTO oripa
    (name, description, price, stock, max_stock, image)
    VALUES
    (:name, :description, :price, :stock, :max_stock, :image)
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ":name" => $name,
    ":description" => $description,
    ":price" => $price,
    ":stock" => $stock,
    ":max_stock" => $max_stock,
    ":image" => $fileName
]);


// セッション削除
unset($_SESSION["oripa_add"]);


// 商品一覧へ
header("Location: oripa_list.php");
exit;

?>
