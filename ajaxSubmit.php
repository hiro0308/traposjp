<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　メッセージ送信ページ　　　　　　　「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if(!empty($_POST) && isset($_SESSION['login_date'])) {
	debug('POST情報：'.print_r($_POST,true));
	
	$msg = $_POST['msg'];
	$boardId = $_POST['boardId'];
	
	//バリデーションチェック
	//未入力チェック
	validRequired($msg, 'msg');
	//最大文字数チェック
	validMaxLen($msg, 'msg');
	
	if(empty($err_msg)) {
		debug('バリデーションチェックOK');
		
		//例外処理
		try {
			//DBへ接続
			$dbh = dbConnect();
			//クエリ作成
			$sql = 'INSERT INTO message(board_id, send_user, msg, send_date, create_date) VALUES(:b_id, :send_user, :msg, :send_date, :create_date)';
			//流し込みデータ
			$data = array(':b_id' => $boardId,
										':send_user' => $_SESSION['user_id'],
										'msg' => $msg,
										'send_date' => date('Y-m-d H:i:s'),
										'create_date' => date('Y-m-d H:i:s'));
			//クエリ実装
			$stmt = queryPost($dbh,$sql,$data);
			
			if($stmt) {
				//ユーザー情報を取得
				$user = getUser($_SESSION['user_id']);
				//html作成
				$html = '<div class="p-commentList__container p-commentList--right">
					<img src="' .showImg(sanitize($user['pic'])). '" class="p-commentList__img">
					<p class="p-commentList__msg">
						<span class="u-triangle p-commentList__triangle"></span>'.
						$msg
					.'</p>
				</div>';
				echo json_encode(array('data' => $html));
			}else {
				return false;
			}
		} catch (\Exception $e) {
			error_log('エラー発生：'.$e->getMessage());
		}
	}
}




 ?>