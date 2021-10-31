<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　プロフィール編集ページ　　　　「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//DBからユーザー情報を取得
$dbFormData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($dbFormData,true));

//POSTされた場合
if(!empty($_POST)) {
	debug('POST情報：'.print_r($_POST,true));
	debug('FILE情報：'.print_r($_FILES,true));
	
	$pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'common') : '';
	//画像をPOSTしていないが、既にDBに登録がある場合はDBのパスを入れる
	$pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;
	$username = $_POST['username'];
	$comment = $_POST['comment'];
	$email = $_POST['email'];
	
	//POST情報とDBの情報とで違えばバリデーションチェック
	if($username !== $dbFormData['username']) {
		//未入力チェック
		validRequired($username,'username');
		//最大文字数チェック
		validMaxLen($username, 'username');
	}
	
	if($comment !== $dbFormData['comment']) {
		//最大文字数チェック
		validMaxLen($comment, 'comment', 500);
	}
	
	if($email !== $dbFormData['email']) {
		//最大文字数チェック
		validMaxLen($email, 'email');
		//Email形式チェック
		validEmail($email,'email');
		//Email重複チェック
		validEmailDup($email,'email');
		//未入力チェック
		validRequired($email,'email');
	}
	
	if(empty($err_msg)) {
		debug('バリデーションOK');
		//例外処理
		try {
			//DBへ接続
			$dbh = dbConnect();
			//クエリ作成
			$sql = 'UPDATE users SET username = :username, comment = :comment, email = :email, pic = :pic WHERE id = :id AND delete_flg = 0';
			//流し込みデータ
			$data = array(':username' => $username, ':comment' => $comment, ':email' => $email, 'pic' => $pic, ':id' => $_SESSION['user_id']);
			//クエリ実行
			$stmt = queryPost($dbh,$sql,$data);
			
			if($stmt) {
				$_SESSION['msg_success'] = SUC01;
				
				debug('セッション変数の中身：'.print_r($_SESSION,true));
				debug('マイページへ遷移します');
				
				header('Location:mypage.php');
			}else {
				$err_msg['common'] = MSG08;
			}
		} catch (\Exception $e) {
			error_log('エラー発生：'.$e->getMessage());
			$err_msg['common'] = MSG04;
		}
	}
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'プロフィール編集';
require('head.php');
?>
<body>
	<!-- ヘッダー -->
	<?php require('header.php'); ?>
	
	<div class="l-content l-content--2column l-container">
		<!-- サイドバー -->
		<?php require('sidebar_menu.php'); ?>
		
		<main class="l-main">
			<h1 class="c-siteTitle p-auth__title">プロフィール編集</h1>
			<div class="l-container--line">
				<p class="p-auth__announce">
					<span class="p-auth__announce__guide">※</span>は必須項目です。
				</p>
				<div class="u-attention u-attention--common">
					<?php echo getErrMsg('email'); ?>
				</div>
				<form action="" class="c-form p-auth" method="post" enctype="multipart/form-data">
					
					<div class="p-auth__space">
						アイコン
						<label class="p-auth__label--drop js-drop-area p-auth__label--line <?php echo appendClass('pic'); ?>">
							画像をアップロードする(3MB未満)
							<div class="p-auth__label__upload">
								ファイルを選択
							</div>
							<input type="hidden" value="3145728">
							<input type="file" name="pic" class="p-auth__inputFile js-inputFile">
							<img src="<?php echo getFormData('pic'); ?>" class="p-auth__img js-set-img" style="<?php if(empty(getFormData('pic')))echo 'display: none;'; ?>">
						</label>
						<div class="u-attention u-attention--uploadImg">
							<?php echo getErrMsg('pic'); ?>
						</div>
					</div>
					
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('username'); ?>">
						ユーザー名<span class="p-auth__announce__guide">※</span>
						<input type="text" name="username" class="c-form__inputText p-auth__inputText" value="<?php echo getFormData('username'); ?>">
						<div class="u-attention">
							<?php echo getErrMsg('username'); ?>
						</div>
					</label>

					
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('comment'); ?>">
						自己紹介
						<textarea name="comment" class="c-form__textArea" placeholder="255文字以内" value="<?php echo getFormData('comment'); ?>"><?php echo getFormData('comment'); ?></textarea>
						<div class="u-attention">
							<?php echo getErrMsg('comment'); ?>
						</div>
					</label>
					
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('email'); ?>">
						メールアドレス<span class="p-auth__announce__guide">※</span>
						<input type="text" name="email" class="c-form__inputText p-auth__inputText" value="<?php echo getFormData('email'); ?>">
						<div class="u-attention">
							<?php echo getErrMsg('email'); ?>
						</div>
					</label>
				
					<input type="submit" value="更新" class="c-btn c-btn--primary p-auth__login__btn">
				</form>
			</div>
		</main>
	</div>
		
	<!-- フッター -->
	<?php require('footer.php'); ?>
</body>
</html>