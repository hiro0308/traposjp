<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　パスワード変更ページ　　　　　「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');
//DBからユーザー情報を取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報；'.print_r($userData,true));

//POSTされた場合
if(!empty($_POST)) {
	debug('POST情報：'.print_r($_POST,true));
	
	$pass_old = $_POST['pass_old'];
	$pass_new = $_POST['pass_new'];
	$pass_new_re = $_POST['pass_new_re'];
	
	//未入力チェック
	validRequired($pass_old,'pass_old');
	validRequired($pass_new,'pass_new');
	validRequired($pass_new_re,'pass_new_re');

	
	if(empty($err_msg)) {
		//パスワードチェック
		validPass($pass_old,'pass_old');
		validPass($pass_new,'pass_new');
		
		if(!password_verify($pass_old, $userData['password'])) {
			$err_msg['pass_old'] = MSG10;
		}
		
		if($pass_old === $pass_new) {
			$err_msg['pass_new'] = MSG11;
		}
		//新しいパスワードと再入力パスワードが同じかチェック
		validMatch($pass_new,$pass_new_re,'pass_new_re');
		
		if(empty($err_msg)) {
			debug('バリデーションチェックOK');
			//例外処理
			try {
				//DBへ接続
				$dbh = dbConnect();
				//クエリ作成
				$sql = 'UPDATE users SET password = :pass WHERE id = :id';
				//流し込みデータ
				$data = array(':pass' => password_hash($pass_new, PASSWORD_DEFAULT), ':id' => $_SESSION['user_id']);
				//クエリ実行
				$stmt = queryPost($dbh,$sql,$data);
				
				if($stmt) {
					$_SESSION['msg_success'] = SUC02;
					
					//メールを送信
					$username = (!empty($userData['username'])) ? $userData['username'] : '名無し';
					$from = EMAIL_ADDRESS;
					$subject = 'パスワード変更通知 | TraPos.JP';
					$to = $userData['email'];
					$comment = <<<EOT
{$username}　さん
パスワードが変更されました。

===============================
TraPos.JP
URL：https://traposjp.hiro-app.net/index.php
==============================
EOT;
				sendMail($to,$subject,$comment,$from);
				
				debug('マイページへ遷移します');
				header('Location:mypage.php');
				}
			} catch (\Exception $e) {
				error_log('エラー発生：'.$e->getMessage());
				$err_msg['common'] = MSG04;
			}
		}
	}
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
 ?>
<?php
$siteTitle = 'パスワード変更';
require('head.php');
 ?>
<body>
	<!-- ヘッダー -->
	<?php require('header.php'); ?>
	
	<div class="l-content l-content--2column l-container">
		<!-- サイドバー -->
		<?php require('sidebar_menu.php'); ?>
		
		<main class="l-main">
			<h1 class="c-siteTitle p-auth__title">パスワード変更</h1>
			<div class="l-container--line">
				<p class="p-auth__announce">
					現在のパスワードと新しいパスワードを入力して下さい。
					<span class="p-auth__announce__guide">※</span>は必須項目です。
				</p>
				<div class="u-attention u-attention--common">
					<?php echo getErrMsg('common'); ?>
				</div>
				<form action="" class="c-form p-auth" method="post">
					
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('pass_old'); ?>">
						現在のパスワード<span class="p-auth__announce__guide">※</span>
						<input type="text" name="pass_old" class="c-form__inputText p-auth__inputText" value="<?php echo getFormData('pass_old'); ?>">
						<div class="u-attention">
							<?php echo getErrMsg('pass_old'); ?>
						</div>
					</label>
					
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('pass_new'); ?>">
						新しいパスワード<span class="p-auth__announce__guide">※</span>
						<input type="password" name="pass_new" class="c-form__inputText p-auth__inputText" value="<?php echo getFormData('pass_new'); ?>">
						<div class="u-attention">
							<?php echo getErrMsg('pass_new'); ?>
						</div>
					</label>

					<label class="p-auth__label p-auth__label--line <?php echo appendClass('pass_new_re'); ?>">
						新しいパスワード（確認用）<span class="p-auth__announce__guide">※</span>
						<input type="password" name="pass_new_re" class="c-form__inputText p-auth__inputText" value="<?php echo getFormData('pass_new_re'); ?>">
						<div class="u-attention">
							<?php echo getErrMsg('pass_new_re'); ?>
						</div>
					</label>
					
					<input type="submit" value="更新する" class="c-btn c-btn--primary p-auth__login__btn">
				</form>
			</div>
		</main>
	</div>
		
	<!-- フッター -->
	<?php require('footer.php'); ?>
</body>
</html>