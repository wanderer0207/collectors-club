<?php

    function sanitize($s){
        return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
    }

    function db_conn3(){
        // Renderの環境変数から取得（設定されていない場合はローカル用のデフォルト値や空にする）
        $host     = getenv('DB_HOST') ?: 'localhost';
        $dbname   = getenv('DB_NAME') ?: 'oripa';
        $user     = getenv('DB_USER') ?: 'postgres'; // PostgreSQLのデフォルトユーザー名（Supabaseなら postgres）
        $password = getenv('DB_PASS') ?: '';
        $port     = getenv('DB_PORT') ?: '5432';    // PostgreSQLのデフォルトポート
        
        // PostgreSQL用のDSNに変更 (pgsql:host=...;port=...;dbname=...)
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

        try{
            // エラーモードを例外スローに設定しておくとデバッグしやすいです
            $dbh = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            return $dbh;   
        }
        catch(Exception $e){
            echo "DBに接続できません。<br>";
            // 本番環境ではエラーの中身を詳細に出しすぎない方が安全ですが、開発中はこれでもOKです
            echo $e->getMessage(); 
            exit;
        }
    }

?>