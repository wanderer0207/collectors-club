<?php

    session_start();

    header("Content-Type: application/json; charset=UTF-8");


    // =========================
    // ログイン確認
    // =========================

    if (!isset($_SESSION["id"])) {

        echo json_encode([
            "success" => false,
            "message" => "ログインしてください。"
        ]);

        exit;
    }


    // =========================
    // POST確認
    // =========================

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        echo json_encode([
            "success" => false,
            "message" => "不正なアクセスです。"
        ]);

        exit;
    }


    // =========================
    // オリパID取得
    // =========================

    $oripaId = isset($_POST["oripa"])
        ? (int)$_POST["oripa"]
        : 0;


    if ($oripaId <= 0) {

        echo json_encode([
            "success" => false,
            "message" => "オリパが指定されていません。"
        ]);

        exit;
    }


    // =========================
    // DB接続
    // =========================

    require_once("common.php");

    $conn = db_conn3();


    // =========================
    // オリパ情報取得
    // =========================

    $sql = "
        SELECT id, name, price, stock, max_stock
        FROM oripa
        WHERE id = :oripa_id
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(
        ":oripa_id",
        $oripaId,
        PDO::PARAM_INT
    );

    $stmt->execute();

    $oripa = $stmt->fetch(PDO::FETCH_ASSOC);


    // オリパが存在しない
    if (!$oripa) {

        echo json_encode([
            "success" => false,
            "message" => "オリパが見つかりません。"
        ]);

        exit;
    }


    // =========================
    // オリパの在庫確認
    // =========================

    if ($oripa["stock"] <= 0) {

        echo json_encode([
            "success" => false,
            "message" => "このオリパは売り切れです。"
        ]);

        exit;
    }


    // =========================
    // 価格取得
    // =========================

    $price = (int)$oripa["price"];


    // =========================
    // ユーザー情報取得
    // =========================

    $sql = "
        SELECT id, point
        FROM users
        WHERE id = :id
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(
        ":id",
        $_SESSION["id"],
        PDO::PARAM_INT
    );

    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    // ユーザーが存在しない
    if (!$user) {

        echo json_encode([
            "success" => false,
            "message" => "ユーザーが見つかりません。"
        ]);

        exit;
    }


    // =========================
    // ポイント確認
    // =========================

    if ((int)$user["point"] < $price) {

        echo json_encode([
            "success" => false,
            "message" => "ポイントが足りません。"
        ]);

        exit;
    }


    // =========================
    // 抽選対象カード取得
    // =========================

    $sql = "
        SELECT
            id,
            name,
            rarity,
            image,
            stock,
            weight
        FROM cards
        WHERE oripa_id = :oripa_id
          AND stock > 0
          AND weight > 0
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(
        ":oripa_id",
        $oripaId,
        PDO::PARAM_INT
    );

    $stmt->execute();

    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // カードが存在しない
    if (!$cards) {

        echo json_encode([
            "success" => false,
            "message" => "抽選できるカードがありません。"
        ]);

        exit;
    }


    // =========================
    // 重みの合計を計算
    // =========================

    $totalWeight = 0;

    foreach ($cards as $card) {

        $totalWeight += (int)$card["weight"];
    }


    // =========================
    // 抽選
    // =========================

    $random = random_int(1, $totalWeight);

    $currentWeight = 0;

    $winner = null;


    foreach ($cards as $card) {

        $currentWeight += (int)$card["weight"];

        if ($random <= $currentWeight) {

            $winner = $card;

            break;
        }
    }


    // 念のため
    if (!$winner) {

        echo json_encode([
            "success" => false,
            "message" => "抽選に失敗しました。"
        ]);

        exit;
    }


    // =========================
    // DB更新開始
    // =========================

    try {

        $conn->beginTransaction();


        // =========================
        // ポイント減算
        // =========================

        $newPoint = (int)$user["point"] - $price;

        $sql = "
            UPDATE users
            SET point = :point
            WHERE id = :id
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(
            ":point",
            $newPoint,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ":id",
            $_SESSION["id"],
            PDO::PARAM_INT
        );

        $stmt->execute();


        // =========================
        // 当選カードの在庫を1減らす
        // =========================

        $sql = "
            UPDATE cards
            SET stock = stock - 1
            WHERE id = :card_id
              AND stock > 0
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(
            ":card_id",
            $winner["id"],
            PDO::PARAM_INT
        );

        $stmt->execute();


        // =========================
        // オリパの在庫を1減らす
        // =========================

        $sql = "
            UPDATE oripa
            SET stock = stock - 1
            WHERE id = :oripa_id
              AND stock > 0
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(
            ":oripa_id",
            $oripaId,
            PDO::PARAM_INT
        );

        $stmt->execute();


        // =========================
        // ガチャ履歴を保存
        // =========================

        $sql = "
            INSERT INTO gacha_history
            (
                user_id,
                oripa_id,
                card_id,
                price,
                created_at
            )
            VALUES
            (
                :user_id,
                :oripa_id,
                :card_id,
                :price,
                NOW()
            )
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(
            ":user_id",
            $_SESSION["id"],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ":oripa_id",
            $oripaId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ":card_id",
            $winner["id"],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ":price",
            $price,
            PDO::PARAM_INT
        );

        $stmt->execute();


        // =========================
        // 全部成功したら確定
        // =========================

        $conn->commit();


    } catch (Exception $e) {

        // 途中でエラーになったら
        // ポイントなどを元に戻す

        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        echo json_encode([
            "success" => false,
            "message" => "ガチャ処理中にエラーが発生しました。"
        ]);

        exit;
    }


    // =========================
    // セッションのポイント更新
    // =========================

    $_SESSION["point"] = $newPoint;


    // =========================
    // 当選メッセージ
    // =========================

    $rarity = $winner["rarity"];

    if ($rarity === "SSSR") {
        $message = "神域到達 — GRAND JACKPOT";
        $effect = "mythic";
    } elseif (in_array($rarity, ["SSR", "SAR"], true)) {
        $message = "プレミアムボーナス！";
        $effect = "premium";
    } elseif (in_array($rarity, ["SR", "AR"], true)) {
        $message = "チャンス成功！";
        $effect = "chance";
    } else {
        $message = "カードを獲得しました";
        $effect = "standard";
    }


    // =========================
    // 結果をJSONで返す
    // =========================

    echo json_encode([

        "success" => true,

        "oripa" => [
            "id" => $oripa["id"],
            "name" => $oripa["name"]
        ],

        "card" => [
            "id" => $winner["id"],
            "name" => $winner["name"],
            "rarity" => $winner["rarity"],
            "image" => $winner["image"]
        ],

        "message" => $message,

        "effect" => $effect,

        "point" => $newPoint

    ], JSON_UNESCAPED_UNICODE);

    exit;
