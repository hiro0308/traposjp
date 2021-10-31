<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　　マイページ　　　　　　「「「「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//DBからユーザー情報を取得
$dbUserData = getUser($_SESSION['user_id']);
//DBから自分の投稿データを取得
$dbPostData = getMyPosts($_SESSION['user_id']);
//DBから自分のお気に入り情報を取得
$dbFavData = getMyFav($_SESSION['user_id']);
//DBから掲示板とメッセージ情報を取得
$dbBoardData = getMyMsgsAndBoards($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($dbUserData,true));
debug('取得した投稿情報：'.print_r($dbPostData,true));
debug('取得したお気に入り情報：'.print_r($dbFavData,true));
debug('取得した掲示板情報：'.print_r($dbBoardData,true));

debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'マイページ';
require('head.php');
?>
<body>
	<!-- ヘッダー -->
	<?php require('header.php'); ?>
	
	<p class="u-slide-msg" id="js-show-msg">
		<?php echo getSessionFlash('msg_success'); ?>
	</p>
	
	<div class="l-content l-content--2column l-container">
		<!-- サイドバー -->
		<?php require('sidebar_menu.php'); ?>
		
		<main class="l-main">
			<div class="p-userInfo">
				<div class="p-userInfo__prof">
					<div class="p-userInfo--left">
						<img src="<?php echo (!empty($dbUserData['pic'])) ? sanitize($dbUserData['pic']) : 'img/header_logo.png'; ?>" class="p-userInfo__img">
					</div>
					<div class="p-userInfo--right">
						<div class="p-userInfo__name"><?php echo sanitize($dbUserData['username']); ?></div>
						<div class="p-userInfo__intro">
							<?php echo sanitize($dbUserData['comment']); ?>
						</div>
					</div>
				</div>
			</div>
				
				<div class="p-postTab">
					<ul class="p-postTab__list">
						<li class="p-postTab__item active js-tab-switch">投稿</li>
						<li class="p-postTab__item js-tab-switch">コメント</li>
						<li class="p-postTab__item js-tab-switch">いいね</li>
					</ul>
				</div>
				
				<div class="p-viewPost js-toggle-content">
					<ul class="p-viewPost__list">
						<?php foreach ($dbPostData['data'] as $key => $val): ?>
							<li class="p-viewPost__item">
								<a href="registPost.php?p_id=<?php echo sanitize($val['id']); ?>" class="p-viewPost__link">
									<img src="<?php echo showImg(sanitize($val['pic1'])); ?>" class="p-viewPost__img">
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				
				<div class="p-postMsg js-toggle-content u-hide">
					<ul class="p-postMsg__list">
						<?php foreach ($dbBoardData as $key => $val): ?>
							<?php if(!empty($val['msg'])): ?>
								<?php $msg = array_shift($val['msg']); ?>
								<?php $username = array_shift($val['user']); ?>
									<li class="p-postMsg__item">
										<a href="postDetail.php?p_id=<?php echo $val['p_id']; ?>&b_id=<?php echo $val['id']; ?>" class="p-postMsg__link">
											<div class="p-postMsg--left">
												<img src="<?php echo showImg(sanitize($val['user']['pic'])); ?>" class="p-postMsg__avatar">
											</div>
											<div class="p-postMsg--right">
												<div class="p-postMsg--top">
													<p class="p-postMsg__name"><?php echo sanitize($username); ?></p>
													<p class="p-postMsg__date"><?php echo sanitize(date('Y.m.d',strtotime($msg['send_date']))); ?></p>
												</div>
												<p class="p-postMsg__msg"><?php echo (mb_strlen($msg['msg']) > 40) ? mb_substr(sanitize($msg['msg']),0 ,40).'...' : $msg['msg']; ?></p>
											</div>
										</a>
									</li>
							<?php else: ?>
								<p>まだコメントをしていません</p>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
				
				<div class="p-viewPost js-toggle-content u-hide">
					<ul class="p-viewPost__list">
						<?php foreach ($dbFavData as $key => $val): ?>
							<li class="p-viewPost__item">
								<a href="postDetail.php?p_id=<?php echo $val['id']; ?>&b_id=<?php echo $val['b_id']; ?>" class="p-viewPost__link">
									<img src="<?php echo showImg(sanitize($val['pic1'])); ?>" class="p-viewPost__img">
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
		</main>
	</div>
		
		<!-- フッター -->
		<?php require('footer.php'); ?>
</body>
</html>