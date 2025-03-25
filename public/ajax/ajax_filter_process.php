<?php
// 選ばれた担当者が含まれるデータを取得：process（表示、非表示）

$host = '127.0.0.1:3306';   // データベース接続
$dbname = 'scheduler';        // データベース名
$dbuser = 'root';           // ユーザー名
$dbpass = 'andadmin';       // パスワード

// process_infoのnameを取得したい
$worker_name = $_POST['worker_name'];

// データベース接続クラスPDOのインスタンス$dbhを作成する
try {
    $dbh = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass);
    // PDOExceptionクラスのインスタンス$eからエラーメッセージを取得
} catch (PDOException $e) {
    // 接続できなかったらvar_dumpの後に処理を終了する
    var_dump($e->getMessage());
    exit;
}

//  受け取ったworker_nameが含まれるテーブルを取得
$sql = "SELECT * FROM process_info WHERE worker = '$worker_name'";

// SQLをセット
$stmt = $dbh->prepare($sql);

// SQLを実行
$stmt->execute();

// 取得したデータを格納
$process_info_worker = $stmt->fetchAll(PDO::FETCH_ASSOC);

// データが存在しない場合の処理
if (empty($process_info_worker)) {
    echo json_encode(['error' => '該当するデータがありません。']);
    exit;
}

// 担当者の含まれる、process_infoのデータが複数ある時
foreach ($process_info_worker as $process) 
{
    // 他のデータを取得しやすいように格納
    $excel_info_id = $process['excel_info_id'];

    // 対応するexcel_infoのデータを取得--------------------------------------------------------------------------------------
    $excel_info = "SELECT * FROM excel_info WHERE id = '$excel_info_id'";

    // SQLをセット
    $stmt_ei = $dbh->prepare($excel_info);

    // SQLを実行
    $stmt_ei->execute();

    // 取得したデータを格納
    $excel_info = $stmt_ei->fetchAll(PDO::FETCH_ASSOC);

    // 配列に格納-----------------------------------------------------------------------------------------------------------
    $excel_infos[] = $excel_info;
}

// jsonで送りやすいように合体（最終的に1種のデータしかないから格納しなくてもOK）
$combined_data = [
    'excel_info'   => $excel_infos,
];

header('Content-type: application/json');       // ヘッダーを指定することによりjsonの動作を安定させる
echo json_encode($combined_data);               // htmlへ渡す配列をjsonに変換する

?>