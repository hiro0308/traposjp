<?php
//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　ユーザー登録ページ　　　　　　「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//POSTされた場合
if(!empty($_POST)) {
	debug('POST情報：'.print_r($_POST,true));
	
	$username = $_POST['username'];
	$email = $_POST['email'];
	$pass = $_POST['pass'];
	
	//未入力チェック
	validRequired($username,'username');
	validRequired($email,'email');
	validRequired($pass,'pass');
	
	if(empty($err_msg)) {
		
		//最大文字数チェック
		validMaxLen($username,'username');
		
		//Email形式チェック
		validEmail($email,'email');
		//Email重複チェック
		validEmailDup($email,'email');
		//最大文字数チェック
		validMaxLen($email,'email');
		
		//パスワードチェック
		validPass($pass,'pass');
		
		if(empty($err_msg)) {
			debug('バリデーションOK');
			
			//例外処理
			try {
				//DBへ接続
				$dbh = dbConnect();
				//クエリ作成
				$sql = 'INSERT INTO users(username, password, email, create_date) VALUES(:username, :password, :email, :date)';
				//流し込みデータ
				$data = array(':username' => $username,
			 								':password' => password_hash($pass, PASSWORD_DEFAULT),
											':email' => $email,
											':date' => date('Y-m-d H:i:s'));
				//クエリ実行
				$stmt = queryPost($dbh,$sql,$data);
				
				if($stmt) {
					//ログイン有効期限
					$sesLimit = 60*60;
					//最終ログイン日時を現在日時に
					$_SESSION['login_date'] = time();
					$_SESSION['login_limit'] = $sesLimit;
					//ユーザーIDを格納
					$_SESSION['user_id'] = $dbh->lastInsertId();
					
					debug('セッション変数の中身：'.print_r($_SESSION,true));
					
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
$siteTitle = 'ユーザー登録';
require('head.php');
?>
<body>
	<!-- ヘッダー -->
	<?php require('header.php'); ?>
	<!-- メインコンテンツ -->
	<div class="l-content">
		<main class="l-main">
			<div class="l-container l-container--line">
				<h1 class="c-siteTitle p-auth__title">新規会員登録</h1>
				<p class="p-auth__announce">
					会員になると、マイページから様々な機能が使えるようになります。<br>
					初めてご利用の方は、以下の項目を入力して会員登録して下さい。<span class="p-auth__announce__guide">※</span>は必須項目です。
				</p>
				<div class="u-attention u-attention--common">
					<?php echo getErrMsg('common'); ?>
				</div>
				<form class="c-form p-auth" method="post">
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('username'); ?>">
						お名前<span class="p-auth__announce__guide">※</span>
						<input type="text" name="username" value="<?php echo getFormData('username'); ?>" class="c-form__inputText p-auth__inputText">
						<div class="u-attention">
							<?php echo getErrMsg('username'); ?>
						</div>
					</label>
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('email'); ?>">
						メールアドレス<span class="p-auth__announce__guide">※</span>
						<input type="text" name="email" value="<?php echo getFormData('email'); ?>" class="c-form__inputText p-auth__inputText">
						<div class="u-attention">
							<?php echo getErrMsg('email'); ?>
						</div>
					</label>
					<label  class="p-auth__label p-auth__label--line <?php echo appendClass('pass'); ?>">
						パスワード<span class="p-auth__announce__guide">※</span>
						<input type="password" name="pass" value="<?php echo getFormData('pass'); ?>" class="c-form__inputText">
						<div class="u-attention">
							<?php echo getErrMsg('pass'); ?>
						</div>
					</label>
				
					<input type="submit" value="登録する" class="c-btn c-btn--primary p-auth__login__btn">
					<a href="login.php" class="p-auth__signupLink">ログインはコチラ</a>
				</form>
			</div>
		</main>
	</div>
	<!-- フッター -->
	<?php require('footer.php'); ?>
</body>
</html>