<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　お気に入りページ　　　　　　　「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if(isset($_POST['postId']) && isset($_SESSION['login_date'])) {
	debug('POST情報：'.print_r($_POST,true));
	
	$p_id = $_POST['postId'];
	//例外処理
	try {
		//お気に入り登録・削除
		//========================================
		//DBへ接続
		$dbh = dbConnect();
		//クエリ作成
		$sql = 'SELECT count(*) FROM favorite WHERE post_id = :p_id AND user_id = :u_id AND delete_flg = 0';
		//流し込みデータ
		$data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id']);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		//クエリの結果を取得
		$rst = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if($stmt) {
			if(!empty(array_shift($rst))) {
				debug('お気に入り登録済です');
				debug('お気に入りを削除します');
				//クエリ作成
				$sql = 'DELETE FROM favorite WHERE post_id = :p_id AND user_id = :u_id';
				//流し込みデータ
				$data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id']);
			}else {
				debug('お気に入り登録していません');
				debug('お気に入り登録します');
				//クエリ作成
				$sql = 'INSERT INTO favorite(post_id, user_id, create_date) VALUES (:p_id, :u_id, :date)';
				//流し込みデータ
				$data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
			}
				debug('SQL：'.$sql);
				//クエリ実行
				$stmt = queryPost($dbh,$sql,$data);
				
				if(!$stmt) {
					return false;
				}
				//表示件数用
				//==================================
				//クエリ作成
				$sql = 'SELECT * FROM favorite WHERE post_id = :p_id AND delete_flg = 0';
				//流し込みデータ
				$data = array(':p_id' => $p_id);
				//クエリ実行
				$stmt = queryPost($dbh,$sql,$data);
				//クエリの結果を取得
				$rst = $stmt->rowCount();
				
				echo json_encode(array('data' => $rst));
		}else {
			return false;
		}

	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
	}
	
}

 ?>