<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　　退会ページ　　　　　　「「「「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//POSTされた場合
if(!empty($_POST)) {
	debug('POST情報：'.print_r($_POST,true));

	//例外処理
	try {
		//DBへ接続
		$dbh = dbConnect();
		//クエリ作成
		$sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
		$sql2 = 'UPDATE post SET delete_flg = 1 WHERE user_id = :u_id';
		$sql3 = 'UPDATE favorite SET delete_flg = 1 WHERE user_id = :u_id';
		//流し込みデータ
		$data = array(':u_id' => $_SESSION['user_id']);
		//クエリ実行
		$stmt1 = queryPost($dbh,$sql1,$data);
		$stmt2 = queryPost($dbh,$sql2,$data);
		$stmt3 = queryPost($dbh,$sql3,$data);
		
		if($stmt1 && $stmt2 && $stmt3) {
			debug('セッションを削除します');
			//セッション削除
			session_destroy();
			
			debug('セッション変数の中身：'.print_r($_SESSION,true));
			debug('ユーザー登録ページへ遷移します');
			
			header('Location:signup.php');
		}
	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
		$err_msg['common'] = MSG04;
	}
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '退会';
require('head.php');
?>
<body>
	<!-- ヘッダー -->
	<?php require('header.php'); ?>
	
	<div class="l-content">
		<main class="l-main">
			<div class="l-container l-container--line">
				<h1 class="c-siteTitle p-auth__title p-auth__title--withdraw">退会</h1>
				<p class="p-auth__announce p-auth__announce--underline">一度退会するとアカウント情報や投稿記事は全て削除されます。</p>
				<form action="" class="c-form p-auth" method="post">
					<input type="submit" value="退会する" class="c-btn c-btn--primary p-auth__login__btn" name="submit">
				</form>
			</div>
		</main>
	</div>
	
	<!-- フッター -->
	<?php require('footer.php'); ?>
</body>
</html>