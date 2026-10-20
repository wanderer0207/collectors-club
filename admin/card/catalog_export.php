
<?php
session_start();

/*
 * 管理者認証がある場合は、既存の管理者認証処理をここに追加する。
 * このファイルを一般公開しないこと。
 */

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$apiKey = getenv("PTCG_API_KEY") ?: "";
$baseUrl = "https://api.pokemontcgapi.com/v1";

$error = "";
$sets = [];
$setCode = trim($_POST["set_code"] ?? "");

/**
 * APIを呼び出してJSONを取得する。
 * APIキーはサーバー側でのみ利用する。
 */
function apiGet(string $url, string $apiKey): array {
    if (!function_exists("curl_init")) {
        throw new RuntimeException(
            "PHPのcURL拡張が有効になっていません。"
        );
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            "X-Api-Key: " . $apiKey,
            "Accept: application/json"
        ],
    ]);

    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        throw new RuntimeException(
            "APIへの接続に失敗しました: " . $curlError
        );
    }

    $data = json_decode($body, true);

    if (!is_array($data)) {
        throw new RuntimeException("APIのJSONを解析できませんでした。");
    }

    if ($status < 200 || $status >= 300) {
        $apiMessage = $data["error"]["message"]
            ?? ("HTTP " . $status);

        throw new RuntimeException(
            "APIエラー: " . $apiMessage
        );
    }

    return $data;
}

if ($apiKey === "") {
    $error =
        "APIキーが設定されていません。"
        . "サーバーの環境変数 PTCG_API_KEY を設定してください。";
} else {
    try {
        // 日本語版の収録商品を新しい順に取得
        $url = $baseUrl
            . "/sets?region=JP&lang=ja&orderBy=-release_date&limit=100";

        $setsData = apiGet($url, $apiKey);
        $sets = $setsData["data"] ?? [];

        // 選択した商品コードの形式を検証
        if (
            $_SERVER["REQUEST_METHOD"] === "POST"
            && $setCode !== ""
        ) {
            if (!preg_match('/^[A-Za-z0-9_-]+$/', $setCode)) {
                throw new RuntimeException(
                    "収録商品コードの形式が正しくありません。"
                );
            }

            // 選択商品が取得した日本語版セット一覧に存在するか確認
            $selectedSet = null;

            foreach ($sets as $set) {
                if (($set["code"] ?? "") === $setCode) {
                    $selectedSet = $set;
                    break;
                }
            }

            if ($selectedSet === null) {
                throw new RuntimeException(
                    "商品一覧に存在しないコードです。再読み込みしてください。"
                );
            }

            $csvRows = [];
            $nextUrl = $baseUrl . "/sets/"
                . rawurlencode($setCode)
                . "/cards?lang=ja&limit=250&include=set";

            $seen = [];

            // 250件ごとに次ページを取得する
            while ($nextUrl !== null && $nextUrl !== "") {
                // APIが返した次ページURL以外にはアクセスしない
                if (
                    !str_starts_with(
                        $nextUrl,
                        $baseUrl . "/sets/"
                    )
                    || parse_url($nextUrl, PHP_URL_HOST)
                        !== parse_url($baseUrl, PHP_URL_HOST)
                ) {
                    throw new RuntimeException(
                        "APIの次ページURLが不正です。"
                    );
                }

                $page = apiGet($nextUrl, $apiKey);

                foreach (($page["data"] ?? []) as $card) {
                    $number = trim((string)($card["number"] ?? ""));
                    $name = trim((string)($card["name"] ?? ""));
                    $rarity = trim((string)($card["rarity"] ?? ""));

                    $cardSetCode = trim((string)(
                        $card["set_code"]
                        ?? $card["set"]["code"]
                        ?? $setCode
                    ));

                    $setName = trim((string)(
                        $card["set"]["name"]
                        ?? $selectedSet["name"]
                        ?? ""
                    ));

                    $apiId = trim((string)($card["id"] ?? ""));

                    if (
                        $number === ""
                        || $name === ""
                        || $cardSetCode === ""
                    ) {
                        continue;
                    }

                    // 同一商品・カード番号の重複を除外
                    $key = $cardSetCode . "|" . $number;

                    if (isset($seen[$key])) {
                        continue;
                    }

                    $seen[$key] = true;

                    // 日本語名の有無をメモに残す
                    $hasJapanese = preg_match(
                        '/[ぁ-んァ-ヶ一-龯]/u',
                        $name
                    );

                    $sourceNote =
                        "Pokémon TCG API; id=" . $apiId
                        . "; image_not_imported";

                    if (!$hasJapanese) {
                        $sourceNote .= "; japanese_name_check_required";
                    }

                    $csvRows[] = [
                        "card_number" => $number,
                        "name" => $name,
                        "rarity" => $rarity,
                        "set_code" => $cardSetCode,
                        "set_name" => $setName,
                        // 画像は取得も保存もしない
                        "image_filename" => "",
                        "source_note" => $sourceNote,
                    ];
                }

                $next = $page["links"]["next"] ?? null;

                if ($next !== null && $next !== "") {
                    $nextHost = parse_url($next, PHP_URL_HOST);

                    if (
                        $nextHost !== parse_url($baseUrl, PHP_URL_HOST)
                    ) {
                        throw new RuntimeException(
                            "次ページの接続先がAPIと一致しません。"
                        );
                    }

                    $nextUrl = $next;
                    usleep(250000);
                } else {
                    $nextUrl = null;
                }
            }

            if (!$csvRows) {
                throw new RuntimeException(
                    "カード情報が0件でした。商品コードを確認してください。"
                );
            }

            // Excelで開きやすいUTF-8 BOM付きCSV
            header("Content-Type: text/csv; charset=UTF-8");
            header(
                'Content-Disposition: attachment; filename="pokemon_'
                . preg_replace('/[^A-Za-z0-9_-]/', '', $setCode)
                . '.csv"'
            );
            header("X-Content-Type-Options: nosniff");

            $out = fopen("php://output", "w");
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                "card_number",
                "name",
                "rarity",
                "set_code",
                "set_name",
                "image_filename",
                "source_note"
            ]);

            foreach ($csvRows as $row) {
                fputcsv($out, array_values($row));
            }

            fclose($out);
            exit;
        }
    } catch (Throwable $e) {
        // APIキーやレスポンス本文などの秘密情報は表示しない
        error_log("catalog_export error: " . $e->getMessage());
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>日本語版カードCSV出力</title>
<link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
<div class="container">
    <h1>日本語版カードCSV出力</h1>

    <?php if ($error !== ""): ?>
        <p class="message"><?= h($error) ?></p>
    <?php endif; ?>

    <p>
        収録商品を選択すると、カード情報をCSVで出力します。
        画像ファイルは取得しません。
    </p>

    <?php if ($sets): ?>
    <form method="post">
        <div class="form-group">
            <label for="set_code">収録商品</label>
            <select id="set_code" name="set_code" required>
                <option value="">商品を選択してください</option>

                <?php foreach ($sets as $set): ?>
                    <option
                        value="<?= h($set["code"] ?? "") ?>"
                        <?= $setCode === ($set["code"] ?? "")
                            ? "selected" : "" ?>
                    >
                        <?= h($set["release_date"] ?? "") ?>
                        -
                        <?= h($set["name"] ?? "") ?>
                        (<?= h($set["code"] ?? "") ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="button" type="submit">
            CSVをダウンロード
        </button>
    </form>
    <?php endif; ?>

    <p><a href="catalog_import.php">CSV一括登録へ</a></p>
    <p><a href="catalog_search.php">カード検索へ</a></p>
</div>
</body>
</html>
