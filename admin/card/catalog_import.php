```php
<?php
session_start();
require_once("../../common.php");

$conn = db_conn3();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = "";
$errors = [];
$successCount = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_FILES["csv"]) ||
        $_FILES["csv"]["error"] !== UPLOAD_ERR_OK
    ) {
        $errors[] = "CSVファイルを選択してください。";

    } else {
        $file = $_FILES["csv"];

        if ($file["size"] > 5 * 1024 * 1024) {
            $errors[] = "CSVは5MB以下にしてください。";

        } else {
            $fp = fopen($file["tmp_name"], "rb");

            if ($fp === false) {
                $errors[] = "CSVを開けませんでした。";

            } else {
                $first = fgets($fp);

                if ($first === false) {
                    $errors[] = "CSVが空です。";

                } else {
                    rewind($fp);

                    $header = fgetcsv($fp, 0, ",", '"', "");

                    if ($header) {
                        $header[0] = preg_replace(
                            '/^\xEF\xBB\xBF/',
                            '',
                            $header[0]
                        );
                    }

                    $required = [
                        "card_number",
                        "name",
                        "rarity",
                        "set_code",
                        "set_name",
                        "image_filename"
                    ];

                    if (!$header || array_diff($required, $header)) {
                        $errors[] = "CSVの列名が正しくありません。";

                    } else {
                        $index = array_flip($header);
                        $rowNumber = 1;

                        $imageDir = realpath(
                            __DIR__ . "/../../images/cards/catalog"
                        );

                        try {
                            $conn->beginTransaction();

                            $sql = "
                                INSERT INTO card_catalog (
                                    card_number,
                                    name,
                                    rarity,
                                    set_code,
                                    set_name,
                                    image_filename,
                                    source_note
                                )
                                VALUES (
                                    :number,
                                    :name,
                                    :rarity,
                                    :set_code,
                                    :set_name,
                                    :image,
                                    :source_note
                                )
                                ON DUPLICATE KEY UPDATE
                                    name = VALUES(name),
                                    rarity = VALUES(rarity),
                                    set_name = VALUES(set_name),
                                    image_filename = VALUES(image_filename),
                                    source_note = VALUES(source_note)
                            ";

                            $stmt = $conn->prepare($sql);

                            while (
                                ($row = fgetcsv(
                                    $fp, 0, ",", '"', ""
                                )) !== false
                            ) {
                                $rowNumber++;

                                // 空行を無視
                                if (
                                    count($row) === 1 &&
                                    trim((string)$row[0]) === ""
                                ) {
                                    continue;
                                }

                                $get = function ($key) use ($row, $index) {
                                    return isset($index[$key])
                                        ? trim(
                                            (string)(
                                                $row[$index[$key]] ?? ""
                                            )
                                        )
                                        : "";
                                };

                                $number = $get("card_number");
                                $name = $get("name");
                                $rarity = $get("rarity");
                                $setCode = $get("set_code");
                                $setName = $get("set_name");
                                $image = $get("image_filename");
                                $source = $get("source_note");

                                // 必須項目を確認
                                if (
                                    $number === "" ||
                                    $name === "" ||
                                    $setCode === ""
                                ) {
                                    throw new RuntimeException(
                                        "{$rowNumber}行目：必須項目が空です。"
                                    );
                                }

                                // 画像がある場合だけ確認する
                                if ($image !== "") {

                                    if (
                                        basename($image) !== $image ||
                                        !preg_match(
                                            '/\.(jpg|jpeg|png|webp)$/i',
                                            $image
                                        )
                                    ) {
                                        throw new RuntimeException(
                                            "{$rowNumber}行目：画像ファイル名が不正です。"
                                        );
                                    }

                                    if ($imageDir === false) {
                                        throw new RuntimeException(
                                            "画像フォルダが見つかりません。"
                                        );
                                    }

                                    $imagePath = $imageDir
                                        . DIRECTORY_SEPARATOR
                                        . $image;

                                    if (!is_file($imagePath)) {
                                        throw new RuntimeException(
                                            "{$rowNumber}行目：画像が見つかりません: {$image}"
                                        );
                                    }
                                }

                                // カード情報を登録・更新
                                $stmt->execute([
                                    ":number" => $number,
                                    ":name" => $name,
                                    ":rarity" => $rarity,
                                    ":set_code" => $setCode,
                                    ":set_name" => $setName,
                                    ":image" => $image,
                                    ":source_note" => $source
                                ]);

                                $successCount++;
                            }

                            $conn->commit();

                            $message =
                                "{$successCount}行を登録・更新しました。";

                        } catch (Throwable $e) {
                            if ($conn->inTransaction()) {
                                $conn->rollBack();
                            }

                            error_log(
                                "catalog_import.php: " .
                                $e->getMessage()
                            );

                            $errors[] =
                                "一括登録を中止しました。CSVの内容と画像を確認してください。";
                        }
                    }
                }

                fclose($fp);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>カード図鑑・一括登録</title>
<link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
<div class="container">
    <h1>カード図鑑・一括登録</h1>

    <?php if ($message !== ""): ?>
        <p class="message"><?= h($message) ?></p>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <p class="message"><?= h($error) ?></p>
    <?php endforeach; ?>

    <p>
        CSVファイルを選択してください。
        画像がある場合は images/cards/catalog/ に置きます。
        画像がないカードは image_filename を空欄にできます。
    </p>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="csv">CSVファイル（最大5MB）</label>
            <input
                type="file"
                id="csv"
                name="csv"
                accept=".csv,text/csv"
                required
            >
        </div>

        <button class="button" type="submit">
            一括登録
        </button>
    </form>

    <p><a href="catalog_search.php">カード検索へ</a></p>
    <p><a href="list.php">既存のカード一覧へ</a></p>
</div>
</body>
</html>
```