<?php

session_start();

require_once("../../common.php");

$conn = db_conn3();


// POSTか確認
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("不正なアクセスです");
}


// ID取得
$id = (int)$_POST["id"];


// 商品情報を取得
$sql = "
    SELECT image
    FROM oripa
    WHERE id = :id
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);


// 商品が存在しない場合
if (!$row) {
    exit("商品が見つかりません");
}


// 画像ファイル名
$fileName = $row["image"];


// =========================
// DBから削除
// =========================

$sql = "
    DELETE FROM oripa
    WHERE id = :id
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ":id" => $id
]);


// =========================
// 画像を削除
// =========================

if (
    !empty($fileName) &&
    file_exists("../../images/" . $fileName)
) {

    unlink("../../images/" . $fileName);

}


// 商品一覧へ
header("Location: oripa_list.php");
exit;

?>
