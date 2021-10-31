<?php

//共通関数・変数の読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「　　トップページ　　　　　　　　「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//GETパラメータを取得
//========================
//カレントページを取得
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;//デフォルトは1とする
//search(検索ワード)を取得
$search = (!empty($_GET['search'])) ? $_GET['search'] : '';
//都道府県
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
//ソート
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';

//表示項目数(投稿数)
$listSpan = 9;
//現在の表示レコード先頭を算出
$currentMinNum = ($currentPageNum-1) * $listSpan;
//DBから投稿データを取得
$dbPostData = getPostList($category,$sort,$search,$currentMinNum,$listSpan);
//DBから都道府県データを取得
$dbCategoryData = getCategory();

// debug('取得した投稿データ：'.print_r($dbPostData,true));

//ページング用
//=====================
//表示項目数
$pageColNum = 5;
//総ページ数
$totalPageNum = (!empty($dbPostData['total_page'])) ? $dbPostData['total_page'] : 0;

debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'HOME';
require('head.php');
 ?>
<body>
	<!-- ヘッダー -->
	<?php require('header.php'); ?>
	
	<div class="l-content l-content--index l-container">
		<!-- サイドバー -->
		<?php require('sidebar_search.php'); ?>
		
		<main class="l-main">
				
			<div class="p-articleList">
				<ul class="p-articleList__list">
					<?php 	if(!empty($dbPostData['data'])): ?>
						<?php foreach ($dbPostData['data'] as $key => $val): ?>
							<li class="p-articleList__item">
								<a href="postDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'].'&b_id='.$val['b_id'] : '?p_id='.$val['id'].'&b_id='.$val['b_id']; ?>"
									class="p-articleList__link"
								>
									<figure class="p-articleList__head">
										<img src="<?php echo showImg(sanitize($val['pic1'])); ?>" class="p-articleList__img">
									</figure>
									<div class="p-articleList__body">
										<img src="<?php echo showImg(sanitize($val['pic'])); ?>" class="p-articleList__avatar">
										<span class="p-articleList__name"><?php echo sanitize($val['username']); ?></span>
									</div>
									<div class="p-articleList__foot">
										<p class="p-articleList__title"><?php echo sanitize($val['title']); ?></p>
										<span class="p-articleList__tag"><?php echo sanitize($val['place']); ?></span>
									</div>
								</a>
							</li>
						<?php endforeach; ?>
						<?php else: ?>
							<p class="p-articleList__no__msg">お探しの投稿は見つかりませんでした</p>
					<?php endif; ?>
				</ul>
			</div>
			
      <div class="c-pagination">
				<ul class="c-pagination__list">
				<?php
					if($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum) {
						$minPageNum = $currentPageNum - 4;
						$maxPageNum = $currentPageNum;
						//現在ページ数が総ページ数の１ページ前で、総ページ数が表示項目数以上の場合
					}elseif($currentPageNum == $totalPageNum - 1 && $totalPageNum >= $pageColNum) {
						$minPageNum = $currentPageNum - 3;
						$maxPageNum = $currentPageNum + 1;
						//現在ページ数が2ページ目で、総ページ数が表示項目数以上の場合
					}elseif($currentPageNum == 2 && $totalPageNum >= $pageColNum) {
						$minPageNum = $currentPageNum - 1;
						$maxPageNum = $currentPageNum + 3;
						//現在ページ数が１ページ目で、総ページ数が表示項目数以上の場合
					}elseif($currentPageNum == 1 && $totalPageNum >= $pageColNum) {
						$minPageNum = $currentPageNum;
						$maxPageNum = $currentPageNum + 4;
						//総ページ数が表示項目数未満の場合
					}elseif($totalPageNum < $pageColNum) {
						$minPageNum = 1;
						$maxPageNum = $totalPageNum;
						//それ以外は左右に2つずつ出す
					}else {
						$minPageNum = $currentPageNum - 2;
						$maxPageNum = $currentPageNum + 2;
					}
			 ?>
			 	<?php if($currentPageNum != 1): ?>
					<li class="c-pagination__item"><a href="<?php echo (!empty(appendGetParam(array('p')))) ? appendGetParam(array('p')).'&p=1' : '?p=1'; ?>" class="c-pagination__link">&lt;&lt;</a></li>
				<?php endif; ?>
				
				<?php for ($i = $minPageNum; $i <= $maxPageNum; $i++): ?>
					<li class="c-pagination__item"><a href="<?php echo (!empty(appendGetParam(array('p')))) ? appendGetParam(array('p')).'&p='.$i : '?p='.$i; ?>" class="c-pagination__link <?php if($currentPageNum == $i) { echo 'active'; } ?>"><?php echo $i; ?></a></li>
				<?php endfor; ?>
				
				<?php if($currentPageNum != $totalPageNum && $totalPageNum > 1): ?>
					<li class="c-pagination__item"><a href="<?php echo (!empty(appendGetParam(array('p')))) ? appendGetParam(array('p')).'&p='.$maxPageNum : '?p='.$maxPageNum; ?>" class="c-pagination__link">&gt;&gt;</a></li>
				<?php endif; ?>
				</ul>
			</div>
		</main>
	</div>
	
	<!-- フッター -->
	<?php require('footer.php'); ?>
</body>
</html>