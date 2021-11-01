<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　投稿詳細ページ　　　　　　　　「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
// require('auth.php');

//GETパラメータ
//====================================
//投稿IDを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//掲示板IDを取得
$b_id = (!empty($_GET['b_id'])) ? $_GET['b_id'] : '';
//DBから投稿データを取得
$dbPostData = getPostOne($p_id);
//DBから掲示板とメッセージ情報を取得
$dbMsgsAndBoard = getMsgsAndBoard($b_id);
//DBから投稿者のデータを取得
$dbUserData = getUser($dbPostData['user_id']);
//DBからお気に入り情報を取得
// $dbFavData = getFav($_SESSION['user_id'], $p_id);
if(!empty($_SESSION['user_id'])) {
	$dbFavData = getFav($_SESSION['user_id'], $p_id);
}else {
	$dbFavData = getFav(null, $p_id);
}
//DBから投稿者の他の投稿情報を最大5つ取得
$dbSideViewData = getLatestPost($dbUserData['id']);
// debug('投稿ID:'.$p_id);
// debug('掲示板ID：'.$b_id);
// debug('取得した投稿者情報：'.print_r($dbPostData,true));
// debug('取得した掲示板とメッセージ情報：'.print_r($dbMsgsAndBoard,true));
// debug('取得した投稿者情報：'.print_r($dbUserData,true));
// debug('取得したお気に入り情報：'.print_r($dbFavData,true));
// debug('取得した最新の投稿情報5つ：'.print_r($dbSideViewData,true));

//パラメータに投稿IDがあって、投稿情報を取得できなければリダイレクト
if(!empty($p_id) && empty($dbPostData)) {
	debug('パラメータに不正な値が入りました');
	header('Location:index.php');
}
//掲示板情報が取得できなければリダイレクト
if(empty($dbMsgsAndBoard)) {
	debug('パラメータに不正な値が入りました');
	header('Location:index.php');
}
//投稿者情報が取得できなければリダイレクト
if(empty($dbUserData)) {
	debug('パラメータに不正な値が入りました');
	header('Location:index.php');
}

//POSTされた場合
if(!empty($_POST)) {
	debug('POST情報：'.print_r($_POST,true));

	$msg = $_POST['msg'];

	//最大文字数チェック
	validMaxLen($msg,'msg',300);
	//未入力チェック
	validRequired($msg,'msg');

	if(empty($err_msg)) {
		debug('バリデーションチェックOK');

		//例外処理
		try {
			//DBへ接続
			$dbh = dbConnect();
			//クエリ作成
			$sql = 'INSERT INTO message(board_id, send_user, msg, send_date, create_date) VALUES(:board_id, :send_user, :msg, :send_date, :date)';
			//流し込みデータ
			$data = array(':board_id' => $b_id, ':send_user' => $_SESSION['user_id'], ':msg' => $msg, ':send_date' => date('Y-m-d H:i:s'), ':date' => date('Y-m-d H:i:s'));
			//クエリ実行
			$stmt = queryPost($dbh,$sql,$data);

			if($stmt) {
				debug('時画面へ遷移します');
				header('Location:' . $_SERVER['PHP_SELF'] .appendGetParam());
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
$siteTitle = '投稿詳細';
require('head.php');
 ?>
<body>
	<!-- ヘッダー -->
	<?php require('header.php'); ?>
	
	<div class="l-content l-content--2column l-container">
		<!-- サイドバー -->
		<?php require('sidebar_menu.php'); ?>
		
		<main class="l-main">
			<section class="">
				<div class="p-articleDetail">
					<div class="p-articleDetail__head">
						<img src="<?php echo showImg(sanitize($dbPostData['pic1'])); ?>" alt="" class="p-articleDetail__imgMain js-click-img-main">
					</div>
					<div class="p-articleDetail__imgSub">
						<ul class="p-articleDetail__imgSub__list">
							<li class="p-articleDetail__imgSub__item">
								<img src="<?php echo showImg(sanitize($dbPostData['pic1'])); ?>" class="p-articleDetail__img js-click-img-sub">
							</li>
							<li class="p-articleDetail__imgSub__item">
								<img src="<?php echo showImg(sanitize($dbPostData['pic2'])); ?>" class="p-articleDetail__img js-click-img-sub">
							</li>
							<li class="p-articleDetail__imgSub__item">
								<img src="<?php echo showImg(sanitize($dbPostData['pic3'])); ?>" class="p-articleDetail__img js-click-img-sub">
							</li>
						</ul>
					</div>
					
					<div class="p-articleDetail__inner">
						<div class="p-articleDetail__body">
							<i class="fas fa-heart p-articleDetail__favIcon js-click-fav <?php if(!empty($dbFavData['isFav'])) { echo 'active'; } ?>"
								data-postid="<?php echo $dbPostData['id']; ?>">
							</i>
							<h2 class="p-articleDetail__title"><?php echo sanitize($dbPostData['title']); ?></h2>
						</div>
						<span class="p-articleDetail__date">
							投稿日：<?php echo sanitize(date('Y-m-d', strtotime($dbPostData['create_date']))); ?>　
							場所：<?php echo sanitize($dbPostData['place']); ?>
						</span>
					</div>
					
					<div class="p-articleDetail__user">
						<img src="<?php echo (!empty($dbUserData['pic'])) ? sanitize($dbUserData['pic']) : 'img/header_logo.png'; ?>"  class="p-articleDetail__avatar">
						<span class="p-articleDetail__name">
							<?php echo sanitize($dbUserData['username']); ?>
						</span>
					</div>
					
					<div class="p-articleDetail__foot">
						<?php echo sanitize($dbPostData['comment']); ?>
					</div>
				</div>
				
				
				<div class="p-commentList">
					<h2 class="p-commentList__title">メッセージを投稿しよう</h2>
					<div class="p-commentList__inner js-scroll-bottom js-add-msg">
						<div class="p-commentList__head">
							<?php if(!empty($_SESSION['user_id'])): ?>
								<?php foreach ($dbMsgsAndBoard as $key => $val): ?>
									<?php if(!empty($val['msg'])): ?>
										<?php if(isset($val['send_user']) && $val['send_user'] != $_SESSION['user_id']): ?>
											<div class="p-commentList__container p-commentList--left">
												<img src="<?php echo showImg(sanitize($val['user']['pic'])); ?>" class="p-commentList__img">
												<p class="p-commentList__msg">
													<span class="u-triangle p-commentList__triangle"></span>
													<?php echo sanitize($val['msg']); ?>
												</p>
											</div>
										<?php else: ?>
											<div class="p-commentList__container p-commentList--right">
												<img src="<?php echo showImg(sanitize($val['user']['pic'])); ?>" class="p-commentList__img">
												<p class="p-commentList__msg">
													<span class="u-triangle p-commentList__triangle"></span>
													<?php echo sanitize($val['msg']); ?>
												</p>
											</div>
										<?php endif; ?>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php else: ?>
								<div class="p-commentList__no__msg">ログイン後に閲覧可能です</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="p-commentList__foot">
						<form class="p-commentList__form" method="post">
							<input type="text" name="msg" class="p-commentList__input js-submit-form">
							<input type="button" name="submit" class="c-btn c-btn--secondary p-commentList--submit js-click-submit"
										 data-boardid="<?php echo $b_id; ?>" <?php if(empty($_SESSION['user_id'])) echo 'disabled'; ?>
										 value="送信"
							>
						</form>
					</div>
				</div>
			</main>
		</div>
	</div>
	
	<script>
		// 掲示板スクロールビュー
		$('.js-scroll-bottom').animate({scrollTop: $('.js-scroll-bottom')[0].scrollHeight}, 'fast');
	</script>

	<!-- フッター -->
	<?php require('footer.php'); ?>
</body>
</html>