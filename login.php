<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　ログインページ　　　　　　　　「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//POSTされた場合
if(!empty($_POST)) {
	debug('POST情報：'.print_r($_POST,true));
	
	$email = $_POST['email'];
	$pass = $_POST['pass'];
	$pass_re = (!empty($_POST['pass_re'])) ? true : false;
	
	//未入力チェック
	validRequired($email,'email');
	validRequired($pass,'pass');
	
	if(empty($err_msg)) {
		//Email形式チェック
		validEmail($email,'email');
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
				$sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
				//流し込みデータ
				$data = array(':email' => $email);
				//クエリ実行
				$stmt = queryPost($dbh,$sql,$data);
				//クエリの結果を取得
				$rst = $stmt->fetch(PDO::FETCH_ASSOC);
				
				if(!empty($rst) && password_verify($pass, array_shift($rst))) {
					debug('パスワードが一致しました');
					
					//ログイン有効期限
					$sesLimit = 60*60;
					//最終ログイン日時を現在日時に
					$_SESSION['login_date'] = time();
					//ログイン保持にチェックがある場合
					if($pass_re) {
						debug('ログイン保持にチェックがあります');
						$_SESSION['login_limit'] = $sesLimit*24*30;
					}else {
						debug('ログイン保持にチェックはありません');
						$_SESSION['login_limit'] = $sesLimit;
					}
					//ユーザーIDを格納
					$_SESSION['user_id'] = array_shift($rst);
					
					debug('セッション変数の中身：'.print_r($_SESSION,true));
					
					header('Location:mypage.php');
				}else {
					debug('パスワードが一致しません');
					$err_msg['common'] = MSG08;
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
$siteTitle = 'ログイン';
require('head.php');
 ?>
<body>
	<!-- ヘッダー -->
	<?php require('header.php'); ?>
	
	<div class="l-content">
		<main class="l-main">
			<div class="l-container l-container--line">
				<h1 class="c-siteTitle p-auth__title">ログイン</h1>
				<p class="p-auth__announce">アカウントをお持ちの方は、コチラからログインして下さい。<span class="p-auth__announce__guide">※</span>は必須項目です。</p>
				<div class="u-attention u-attention--common">
					<?php echo getErrMsg('common'); ?>
				</div>
				<form action="" class="c-form p-auth" method="post">
					
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('email'); ?>">
						メールアドレス<span class="p-auth__announce__guide">※</span>
						<input type="text" name="email" value="<?php echo getFormData('email'); ?>" class="c-form__inputText p-auth__inputText">
						<div class="u-attention">
							<?php echo getErrMsg('email'); ?>
						</div>
					</label>
					<label  class="p-auth__label p-auth__label--line <?php echo appendClass('pass'); ?>">
						パスワード<span class="p-auth__announce__guide">※</span>
						<input type="password" name="pass" class="c-form__inputText">
						<div class="u-attention">
							<?php echo getErrMsg('pass'); ?>
						</div>
					</label>
					<label class="p-auth__label__checkbox">
						<input type="checkbox" name="pass_re" class="c-form__checkbox"><span class="p-auth__text">ログイン情報を保持する</span>
					</label>
					<input type="submit" value="ログイン" class="c-btn c-btn--primary p-auth__login__btn">
					<a href="passRemindSend.php" class="p-auth__passForget">パスワードを忘れた場合はコチラ</a>
					<a href="signup.php" class="p-auth__signupLink">新規会員登録</a>
				</form>
			</div>
		</main>
	</div>
	<!-- フッター -->
	<?php require('footer.php'); ?>
</body>
</html>