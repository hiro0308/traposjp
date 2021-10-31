<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「「「「「「「「「　投稿ページ　　　　　「「「「「「「「「「「「「「「「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//GETパラメータから投稿IDを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//DBから投稿データを取得
$dbFormData = (!empty($p_id)) ?  getPost($p_id, $_SESSION['user_id']) : '';
//新規登録画面or編集画面か判断用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
//パラメータの投稿IDがあってDBにデータがなければリダイレクト
if(!empty($p_id) && empty($dbFormData)) {
  debug('パラメータに不正な値が入りました');
  header('Location:mypage.php');
}
//DBからカテゴリー情報を取得
$dbCategoryData = getCategory();


debug('投稿ID：'.$p_id);
debug('取得した投稿情報：'.print_r($dbFormData,true));
// debug('取得したカテゴリーデータ:'.print_r($dbCategoryData,true));



// post送信されていた場合
if(!empty($_POST)){
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES,true));
  
  $title = $_POST['title'];
  $place = $_POST['place_id'];
  $comment = $_POST['comment'];
  $pic1 = (!empty($_FILES['pic1']['tmp_name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
  //POSTデータはないが、既にDBに登録がある場合はDBの情報を格納
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
  $pic2 = (!empty($_FILES['pic2']['tmp_name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
  $pic3 = (!empty($_FILES['pic3']['tmp_name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
  $pic3 = (empty($pic3) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;
  
  if($edit_flg) {
    debug('更新用バリデーション開始');
    if($title !== $dbFormData['title']) {
      //最大文字数チェック
      validMaxLen($title,'title');
      //未入力チェック
      validRequired($title,'title');
    }
    if($place !== $dbFormData['place_id']) {
      //セレクトボックスチェック
      validSelect($place,'place_id');
    }
    if($comment !== $dbFormData['comment']) {
      //最大文字数チェック
      validMaxLen($comment,'comment',500);
      //未入力チェック
      validRequired($comment,'comment');
    }
  }else {
    debug('新規用バリデーション開始');
    //最大文字数チェック
    validMaxLen($title,'title');
    //未入力チェック
    validRequired($title,'title');
    
    //セレクトボックスチェック
    validSelect($place,'place_id');
    
    //最大文字数チェック
    validMaxLen($comment,'comment',500);
    //未入力チェック
    validRequired($comment,'comment');
  }
  
  if(empty($err_msg)) {
    debug('バリデーションOK');
    //例外処理
    try {
      //DBへ接続
      $dbh = dbConnect();
      if($edit_flg) {
        debug('DB更新です');
        //クエリ作成
        $sql = 'UPDATE post SET title = :title, place_id = :place, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE id = :p_id AND user_id = :u_id AND delete_flg = 0';
        //流し込みデータ
        $data = array(':title' => $title, ':place' => $place, ':comment' => $comment,
                      ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':p_id' => $p_id,
                      ':u_id' => $_SESSION['user_id']);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
        
        debug('SQL：'.$sql);
        
        if($stmt) {
          $_SESSION['msg_success'] = SUC04;
          debug('マイページへ遷移します');
          header('Location:mypage.php');
        }
      }else {
        debug('新規登録です');
        //クエリ作成
        $sql = 'INSERT INTO post(title, place_id, comment, pic1, pic2, pic3, user_id, create_date) VALUES(:title, :place, :comment, :pic1, :pic2, :pic3, :u_id, :date)';
        //流し込みデータ
        $data = array(':title' => $title, ':place' => $place, ':comment' => $comment,
                      ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'],
                      ':date' => date('Y-m-d H:i:s'));
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
        
        debug('SQL：'.$sql);
        
        if($stmt) {
          debug('掲示板登録を行います');
          //掲示板作成
          //========================
          //クエリ作成
          $sql ='INSERT INTO board(post_id, user_id, create_date) VALUES (:post_id, :u_id, :create_date)';
          //流し込みデータ
          $data = array(':post_id' => $dbh->lastInsertId(),
                        ':u_id' => $_SESSION['user_id'],
                        ':create_date' => date('Y-m-d H:i:s'));
          //クエリ実行
          $stmt = queryPost($dbh,$sql,$data);
          
          debug('SQL：'.$sql);
          
          if($stmt) {
            $_SESSION['msg_success'] = SUC04;
            debug('マイページへ遷移します');
            header('Location:mypage.php');
          }
        }
      }
    } catch (\Exception $e) {
				error_log('エラー発生：'.$e->getMessage());
				$err_msg['common'] = MSG04;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '投稿ページ';
require('head.php');
?>
<body>
	<!-- ヘッダー -->
	<?php require('header.php'); ?>
	
	<div class="l-content l-content--2column l-container">
		<!-- サイドバー -->
		<?php require('sidebar_menu.php'); ?>
		
		<!-- メインコンテンツ -->
		<main class="l-main">
			<h1 class="c-siteTitle p-auth__title">記事投稿</h1>
			<div class="l-container--line">
				<p class="p-auth__announce">
					旅の思い出を投稿して皆と共有しよう！<span class="p-auth__announce__guide">※</span>は必須項目です。
				</p>
				<div class="u-attention u-attention-common">
					<?php echo getErrMsg('title'); ?>
				</div>
				<form action="" class="c-form p-auth" method="post" enctype="multipart/form-data">

					<label class="p-auth__label p-auth__label--line <?php echo appendClass('title'); ?>">
						タイトル<span class="p-auth__announce__guide">※</span>
						<input type="text" value="<?php echo getFormData('title'); ?>" class="c-form__inputText p-auth__inputText" name="title">
						<div class="u-attention">
							<?php echo getErrMsg('title'); ?>
						</div>
					</label>
					
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('place_id'); ?>">
						場所<span class="p-auth__announce__guide">※</span>
						<select name="place_id" class="c-form__selectBox">
							<option value="0" <?php if(getFormData('place_id') == 0) { echo 'selected'; } ?>>都道府県を選択</option>
							<?php foreach ($dbCategoryData as $key => $val): ?>
								<option value="<?php echo sanitize($val['id']); ?>" <?php if(getFormData('place_id') == $val['id']) { echo 'selected'; } ?>><?php echo sanitize($val['name']); ?></option>
							<?php endforeach; ?>
						</select>
						<div class="u-attention">
							<?php echo getErrMsg('place_id'); ?>
						</div>
					</label>
					
					<label class="p-auth__label p-auth__label--line <?php echo appendClass('comment'); ?>">
						詳細<span class="p-auth__announce__guide">※</span>
						<textarea name="comment" class="c-form__textArea c-form__textArea--l js-count-target"><?php echo getFormData('comment'); ?></textarea>
						<div class="p-auth__count"><span class="js-count-view">0</span>/500</div>
						<div class="u-attention">
							<?php echo getErrMsg('comment'); ?>
						</div>
					</label>
					
					<div class="p-auth__form--group">
						<div class="p-auth__imgCont">
							画像1
							<label class="p-auth__label--drop js-drop-area <?php echo appendClass('pic1'); ?>">
								画像をアップロードする(3MB未満)
								<div class="p-auth__label__upload">
									ファイルを選択
								</div>
								<input type="hidden" value="3145728" name="MAX_FILE_SIZE">
								<input type="file" name="pic1" class="p-auth__inputFile js-inputFile">
								<img src="<?php echo getFormData('pic1'); ?>" class="p-auth__img js-set-img" style="<?php if(empty(getFormData('pic1')))echo 'display: none;'; ?>">
							</label>
							<div class="u-attention u-attention--uploadImg">
								<?php echo getErrMsg('pic1'); ?>
							</div>
						</div>
						
						<div class="p-auth__imgCont">
							画像2
							<label class="p-auth__label--drop js-drop-area <?php echo appendClass('pic2'); ?>">
								画像をアップロードする(3MB未満)
								<div class="p-auth__label__upload">
									ファイルを選択
								</div>
								<input type="hidden" value="3145728" name="MAX_FILE_SIZE">
								<input type="file" name="pic2" class="p-auth__inputFile js-inputFile">
								<img src="<?php echo getFormData('pic2'); ?>" class="p-auth__img js-set-img" style="<?php if(empty(getFormData('pic2')))echo 'display: none;'; ?>">
								<div class="u-attention u-attention--uploadImg">
									<?php echo getErrMsg('pic2'); ?>
								</div>
							</label>
						</div>
						
						<div class="p-auth__imgCont">
							画像3
							<label class="p-auth__label--drop js-drop-area <?php echo appendClass('pic3'); ?>">
								画像をアップロードする(3MB未満)
								<div class="p-auth__label__upload">
									ファイルを選択
								</div>
								<input type="hidden" value="3145728" name="MAX_FILE_SIZE">
								<input type="file" name="pic3" class="p-auth__inputFile js-inputFile">
								<img src="<?php echo getFormData('pic3'); ?>" class="p-auth__img js-set-img" style="<?php if(empty(getFormData('pic3')))echo 'display: none;'; ?>">
								<div class="u-attention u-attention--uploadImg">
									<?php echo getErrMsg('pic3'); ?>
								</div>
							</label>
						</div>
					</div>
					
					<input type="submit" value="<?php echo ($edit_flg) ? '更新する' : '投稿する'; ?>" class="c-btn c-btn--primary p-auth__login__btn">
				</form>
			</div>
		</main>
	</div>
	<!-- フッター -->
	<?php require('footer.php'); ?>
</body>
</html>