<?php

ini_set('display_errors',1);

// データベース接続
$host = '127.0.0.1:3306';
// データベース名
$dbname = 'scheduler';
// ユーザー名
$dbuser = 'root';
// パスワード
$dbpass = 'andadmin';

$update_management_id = $_POST['id'];

// データベース接続クラスPDOのインスタンス$dbhを作成する
try {
    $dbh = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass);
    // PDOExceptionクラスのインスタンス$eからエラーメッセージを取得
} catch (PDOException $e) {
    // 接続できなかったらvar_dumpの後に処理を終了する
    var_dump($e->getMessage());
    exit;
}
//*******製造課をデータベースで検索して製造課IDを取ってくる */
// $sql = "SELECT * FROM factory WHERE name = '$factory_div'";
$sql = "SELECT * FROM update_management WHERE id = '$update_management_id' ";

// SQLをセット
$stmt = $dbh->prepare($sql);

// SQLを実行
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ヘッダーを指定することによりjsonの動作を安定させる
header('Content-type: application/json');

// htmlへ渡す配列をjsonに変換する
echo json_encode($data);