<?php
//ログの設定
//=======================
//ログをとるかどうか
ini_set('log_errors', 'on');
//ログの出力先を指定
ini_set('error_log', 'php.log');

//セッションの設定
//=====================
//セッションファイルの置き場所を変更
session_save_path("/var/tmp");
//ガーベージコレクションを30日経ったものに対して1/100の確率で削除する
ini_set('session.gc_maxlifetime', 60*60*24*30);
//クッキー自体の有効期限を設定
ini_set('session.cookie_lifetime', 60*60*24*30);
//セッションを使用
session_start();
//現在のセッションを新しいIDへ生成
session_regenerate_id();

//デバッグの設定
//=======================
//デバッグフラグ
$debug_flg = false;
//デバッグ関数
function debug($str) {
	global $debug_flg;
	if($debug_flg) {
		error_log('デバッグ：'.$str);
	}
}
//デバッグログ関数
function debugLogStart() {
	debug('<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<画面表示処理開始');
	debug('セッションID：'.session_id());
	debug('セッション変数の中身：'.print_r($_SESSION,true));
	debug('現在日時タイムスタンプ'.time());
	if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
		debug('ログイン期限日時タイムスタンプ：'.($_SESSION['login_date'] + $_SESSION['login_limit']));
	}
}

require_once 'vendor/autoload.php'; //vendorディレクトリの階層を指定する
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); //.envの階層を指定する
$dotenv->load();

//定数
define('MSG01', '入力必須です');
define('MSG02', '文字以内で入力して下さい');
define('MSG03', 'メールアドレスの形式で入力して下さい');
define('MSG04', 'エラーが発生しました。しばらく経ってからやり直して下さい');
define('MSG05', '半角英数字のみご利用いただけます');
define('MSG06', '文字以上で入力して下さい');
define('MSG07', 'そのEmailはすでに登録されています');
define('MSG08', 'メールアドレスまたは、パスワードが間違っています');
define('MSG09', 'パスワードが一致しません');
define('MSG10', '現在のパスワードが違います');
define('MSG11', '現在のパスワードと同じパスワードです');
define('MSG12', '登録済のメールアドレスを入力して下さい');
define('MSG13', '文字で入力して下さい');
define('MSG14', '認証キーが間違っています');
define('MSG15', '認証キーの有効期限が切れています');
define('MSG16', '正しく選択して下さい');
define('SUC01', 'プロフィールを編集しました');
define('SUC02', 'パスワードを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '記事を投稿しました');
// MySQL 設定
define('DB_DATABASE', $_ENV['DB_DATABASE']);
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_USERNAME', $_ENV['DB_USERNAME']);
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);
//メール送信
define('EMAIL_ADDRESS', $_ENV['EMAIL_ADDRESS']);

//エラーメッセージ格納用の配列
$err_msg = array();

//=======================================
//バリデーション関数
//=======================================

//バリデーション関数（未入力チェック）
function validRequired($str, $key){
  if($str === ''){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
//バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 255){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = $max.MSG02;
  }
}
//バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = $min.MSG06;
  }
}
//バリデーション関数（固定長チェック）
function validLength($str,$key, $length = 8) {
	if(mb_strlen($str) !== $length) {
		global $err_msg;
		$err_msg[$key] = $length.MSG13;
	}
}

//バリデーション関数（Email形式チェック）
function validEmail($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
//バリデーション関数（Email重複チェック）
function validEmailDup($str,$key) {
	global $err_msg;
	//例外処理
	try {
		//DBへ接続
		$dbh = dbConnect();
		//SQL文作成
		$sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
		//流し込みデータ
		$data = array(':email' => $str);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		//クエリ結果の値を取得
		$rst = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!empty(array_shift($rst))) {
			$err_msg['email'] = MSG07;
		}
	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
		$err_msg['common'] = MSG04;
	}
}
//バリデーション関数（半角チェック）
function validHalf($str, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
//バリデーション関数（同値チェック）
function validMatch($str1,$str2,$key) {
	if($str1 !== $str2) {
		global $err_msg;
		$err_msg[$key] = MSG09;
	}
}
//バリデーション関数（セレクトボックスチェック）
function validSelect($str,$key) {
	if(!preg_match("/^[1-9]|[1-4][0-7]+/",$str)) {
		global $err_msg;
		$err_msg[$key] = MSG16;
	}
}
//パスワードチェック
function validPass($str, $key){
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}
//エラーメッセージ表示
function getErrMsg($key) {
	global $err_msg;
	if(!empty($err_msg[$key])) {
		return $err_msg[$key];
	}
}


//================================
// データベース
//================================

//DB接続関数
function dbConnect() {
	$dsn = 'mysql:dbname='.DB_DATABASE.';host='.DB_HOST.';charset=utf8';
	$username = DB_USERNAME;
	$password = DB_PASSWORD;
	$options = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
	);
	//PDOオブジェクト作成
	$dbh = new PDO($dsn,$username,$password,$options);
	return $dbh;
}
function queryPost($dbh,$sql,$data) {
	//SQL文作成
	$stmt = $dbh->prepare($sql);
	//クエリ実行
	$stmt->execute($data);
	
	if($stmt) {
		debug('クエリ成功');
		return $stmt;
	}else {
		debug('クエリ失敗');
		debug('失敗したSQL：'.print_r($stmt,true));
		global $err_msg;
		$err_msg['common'] = MSG04;
		return false;
	}
}
//カテゴリー情報取得
function getCategory() {
	debug('カテゴリー情報を取得します');
	//例外処理
	try {
		//DBへ接続
		$dbh = dbConnect();
		//SQL文作成
		$sql = 'SELECT id, name FROM place';
		//流し込みデータ
		$data = array();
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		
		if($stmt) {
			//クエリの結果を取得
			return $stmt->fetchAll();
		}else {
			return false;
		}
		
	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
		$err_msg['common'] = MSG04;
	}
}
//ユーザー情報取得
function getUser($u_id) {
	debug('ユーザー情報を取得します');
	//例外処理
	try {
		//DBへ接続
		$dbh = dbConnect();
		//SQL文作成
		$sql = 'SELECT id, username, comment, password, email, pic, create_date FROM users WHERE id = :id AND delete_flg = 0';
		//流し込みデータ
		$data = array(':id' => $u_id);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		
		if($stmt) {
			//クエリの結果を取得
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}else {
			return false;
		}
		
	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
		$err_msg['common'] = MSG04;
	}
}
//投稿情報を取得
function getPost($p_id, $u_id) {
	debug('投稿情報を取得します');
	debug('投稿ID：'.$p_id);
	debug('ユーザーID:'.$u_id);
	//例外処理
	try {
		//DBへ接続
		$dbh = dbConnect();
		//SQL文作成
		$sql = 'SELECT id, title, place_id, comment, pic1, pic2, pic3, user_id, create_date FROM post WHERE id = :p_id AND user_id = :u_id AND delete_flg = 0';
		//流し込みデータ
		$data = array(':p_id' => $p_id, ':u_id' => $u_id);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		
		if($stmt) {
			//クエリの結果を取得
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}else {
			return false;
		}
	} catch (\Exception $e) {
		error_log('エラー発生:' . $e->getMessage());
	}
}
function getPostList($category,$sort,$search,$currentMinNum,$listSpan) {
	debug('投稿情報を取得します');
	//例外処理
	try {
		//DBへ接続
		$dbh = dbConnect();
		//件数表示用
		//==========================================
		//SQL文作成
		$sql = 'SELECT id FROM post WHERE delete_flg = 0';
		if(!empty($category)) { $sql .= ' AND place_id ='.$category; }

 		if(!empty($search)) {
			$sql = 'SELECT post.id FROM post JOIN place ON place_id = place.id WHERE post.delete_flg = 0 AND name like :search OR comment like :search OR title like :search';
		}

		//流し込みデータ
		$data = array();
		if(!empty($search)) {
			$data = array(':search' => '%'.$search.'%');
		}
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		
		debug('SQL:'.$sql);
		
		//クエリの結果を取得
		$rst['total'] = $stmt->rowCount();//総レコード数
		$rst['total_page'] = ceil($rst['total'] / $listSpan);//総ページ数
		
		if(!$stmt) {
			return false;
		}
		//表示用
		//=========================
		//SQL文作成
		// $sql = 'SELECT post.id, post.title, post.comment, post.pic1, place.name AS place, b.id AS b_id, FROM post JOIN place ON post.place_id = place.id JOIN board AS b ON post.id = b.post_id WHERE post.delete_flg = 0';
		
		$sql = 'SELECT p.id, p.title, p.comment, p.pic1, p.user_id, place.name AS place, b.id AS b_id, u.username, u.pic FROM post AS p JOIN place ON p.place_id = place.id JOIN board AS b ON p.id = b.post_id JOIN users AS u ON u.id = p.user_id WHERE p.delete_flg = 0';
		
		if(!empty($search)) { $sql .= ' AND place.name like :search OR p.comment like :search OR p.title like :search';}
		if(!empty($category)) { $sql .= ' AND place_id = '.$category; }
		if(!empty($sort)) {
			switch ($sort) {
				case 1://降順
					$sql .= ' ORDER BY p.create_date DESC';
					break;
				case 2;//昇順
					$sql .= ' ORDER BY p.create_date ASC';
					break;
			}
		}else {//sortの指定がない場合は常時、降順
			$sql .= ' ORDER BY p.create_date DESC';
		}
		$sql .= ' LIMIT '.$listSpan.' OFFSET '.$currentMinNum;
		//流し込みデータ
		$data = array();
		if(!empty($search)) {
			$data = array(':search' => '%'.$search.'%');
		}
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		
		debug('SQL:'.$sql);
		
		if($stmt) {
			//クエリの結果を取得
			$rst['data'] = $stmt->fetchAll();
			return $rst;
		}else {
			return false;
		}
		
	} catch (\Exception $e) {
		error_log('エラー発生:' . $e->getMessage());
	}
}
function getPostOne($p_id) {
	debug('投稿情報を取得します');
	debug('投稿ID：'.$p_id);
	//例外処理
	try {
		//DBへ接続
		$dbh = dbConnect();
		//SQL文作成
		$sql = 'SELECT post.id, title, place_id, comment, pic1, pic2, pic3, user_id, post.create_date, place.name AS place FROM post LEFT JOIN place ON place_id = place.id WHERE post.id = :p_id AND post.delete_flg = 0';
		//流し込みデータ
		$data = array(':p_id' => $p_id);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		
		if($stmt) {
			//クエリの結果を取得
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}else {
			return false;
		}
	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
		$err_msg['common'] = MSG04;
	}
}
function getLatestPost($id) {
	debug('投稿データを最大5つ取得します');
	//例外処理
	try {
		// DBへ接続
		$dbh = dbConnect();
		//SQL文作成
		$sql = 'SELECT p.id, p.title, p.place_id, p.comment, p.pic1, p.pic2, p.pic3, p.user_id, p.create_date, b.id AS b_id FROM post AS p LEFT JOIN board AS b ON p.id = b.post_id WHERE p.user_id = :u_id AND p.delete_flg = 0';
		$sql .= ' ORDER BY p.create_date desc LIMIT 5 OFFSET 0';
		//流し込みデータ
		$data = array(':u_id' => $id);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		if($stmt) {
			//クエリの結果を取得
			return $stmt->fetchAll();
		}else {
			return false;
		}
		} catch (\Exception $e) {
			error_log('エラー発生:' . $e->getMessage());
	}
}
function getMyPosts($id) {
	debug('自分自身の投稿情報を取得します');
	global $err_msg;
	//例外処理
	try {
		// DBへ接続
		$dbh = dbConnect();
		//SQL文作成
		$sql = 'SELECT id, title, place_id, comment, pic1, pic2, pic3, user_id, create_date FROM post WHERE user_id = :u_id AND delete_flg = 0 ORDER BY create_date DESC';
		//流し込みデータ
		$data = array(':u_id' => $id);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		if($stmt) {
			$rst['data'] = $stmt->fetchAll();
			//取得したデータ件数を変数へ格納
			$rst['total'] = $stmt->rowCount();
			return $rst;
		}else {
			return false;
		}
		} catch (\Exception $e) {
			error_log('エラー発生:' . $e->getMessage());
	}
}
//掲示板とメッセージ情報
function getMsgsAndBoard($b_id) {
	debug('掲示板とメッセージ情報を取得します');
	debug('掲示板ID：'.$b_id);
	//例外処理
	try {
		//掲示板データを取得
		//===========================
		//DBへ接続
		$dbh = dbConnect();
		//SQL文作成
		$sql = 'SELECT b.id, post_id, m.id, board_id, send_user, msg, m.create_date FROM message AS m RIGHT JOIN board AS b ON b.id = board_id WHERE b.id = :b_id AND b.delete_flg = 0';
		//流し込みデータ
		$data = array(':b_id' => $b_id);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		//クエリの結果を全て取得
		$rst = $stmt->fetchAll();
		
		if(!empty($rst)) {
			foreach ($rst as $key => $val) {
				$sql = 'SELECT id, username, pic FROM users WHERE id = :u_id';
				$data = array(':u_id' => $val['send_user']);
				$stmt = queryPost($dbh,$sql,$data);
				$rst[$key]['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
			}
		}
		
		
		if($stmt){
      // クエリ結果の全データを返却
      return $rst;
    }else{
      return false;
    }
		
	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
	}
}
function getMyMsgsAndBoards($u_id) {
	debug('自分の掲示板とメッセージを取得します');
	//例外処理
	try {
		//DBへ接続
		$dbh = dbConnect();
		//自分のメッセージ情報のある、掲示板データと投稿者IDを取得
		//================================
		//SQL文作成
		$sql = 'SELECT distinct b.id, b.post_id AS p_id, b.create_date, p.user_id FROM board AS b LEFT JOIN message AS m ON b.id = m.board_id LEFT JOIN post AS p ON b.post_id = p.id WHERE m.send_user = :u_id ORDER BY create_date DESC';
		//流し込みデータ
		$data = array(':u_id' => $u_id);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		//クエリの結果を全て取得
		$rst = $stmt->fetchAll();
		
		if(!empty($rst)) {
			//取得したユーザーIDを元にユーザー名を取得
			foreach ($rst as $key => $val) {
				//SQL文作成
				$sql = 'SELECT username, pic FROM users WHERE id = :u_id AND delete_flg = 0';
				//流し込みデータ
				$data = array(':u_id' => $val['user_id']);
				//クエリ実行
				$stmt = queryPost($dbh,$sql,$data);
				//クエリの結果を全て取得
				$rst[$key]['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
			}
		}
		
		if(!empty($rst)) {
			foreach ($rst as $key => $val) {
				//取得した掲示板情報を元に、メッセージ情報を取得
				//SQL文作成
				$sql = 'SELECT m.msg, m.send_date FROM message AS m WHERE board_id = :b_id AND delete_flg = 0 ORDER BY send_date DESC';
				//流し込みデータ
				$data = array(':b_id' => $val['id']);
				//クエリ実行
				$stmt = queryPost($dbh,$sql,$data);
				//クエリの結果を全て取得
				$rst[$key]['msg'] = $stmt->fetchAll();
				
			}
		}
		if($stmt){
			// クエリ結果の全データを返却
			return $rst;
		}else{
			return false;
		}
		
	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
	}
}
//お気に入り登録
function getFav($u_id = null,$p_id) {
	debug('ユーザーID：'.$u_id);
	debug('商品ID：'.$p_id);
	debug('お気に入り情報を取得します');
	//例外処理
	try {
		//表示件数用
		//=====
		$dbh = dbConnect();
		// SQL文作成
		$sql = 'SELECT * FROM favorite WHERE post_id = :p_id AND delete_flg = 0';
		//流し込みデータ
		$data = array(':p_id' => $p_id);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		
		if($stmt) {
			//クエリの結果をレコード数で取得
			$rst['favNum'] = $stmt->rowCount();
						
			if($u_id == null) {
				debug('ユーザー情報はありません');
				return $rst;
			}else {
				debug('登録の有無を確認します');
				//登録有無確認用
				//==========================
				//SQL文作成
				$sql = 'SELECT count(*) FROM favorite WHERE post_id = :p_id AND user_id = :u_id AND delete_flg = 0';
				//流し込みデータ
				$data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id']);
				//クエリ実行
				$stmt = queryPost($dbh,$sql,$data);
				//結果を取得
				$rst['isFav'] = $stmt->fetch(PDO::FETCH_ASSOC);
											
				if($stmt) {
					if(!empty(array_shift($rst['isFav']))) {
						debug('お気に入り登録済です');
						$rst['isFav'] = true;
					}else {
						debug('お気に入り未登録です');
						$rst['isFav'] = false;
					}
					return $rst;
				}else {
					return false;
				}
			}
		}
	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
	}
}
function getPostAndFav() {
	debug('お気に入り順から5つ投稿情報を取得します');
	//例外処理
	try {
		//DBへ接続
		$dbh = dbConnect();
		//SQL文作成
		$sql = 'SELECT  post_id FROM favorite LIMIT 5 OFFSET 0';
		//流し込みデータ
		$data = array();
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);
		
		if($stmt) {
			//クエリの結果を全て取得
			return $stmt->fetchAll();
		}else {
			return false;
		}
	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
	}
}
function getMyFav($id) {
	debug('自分自身のお気に入り情報を取得します');
	//例外処理
	try {
		//DBへ接続
		$dbh = dbConnect();
		//SQL文作成
		$sql = 'SELECT f.post_id, f.user_id, p.id, p.title, p.comment, p.pic1, b.id AS b_id FROM favorite AS f LEFT JOIN post AS p ON f.post_id = p.id LEFT JOIN board AS b ON f.post_id = b.post_id  WHERE f.user_id = :u_id AND f.delete_flg = 0';
		$sql .= ' ORDER BY f.create_date DESC';
		//流し込みデータ
		$data = array(':u_id' => $id);
		//クエリ実行
		$stmt = queryPost($dbh,$sql,$data);

		if($stmt) {
			//クエリの結果を全て取得
			return $stmt->fetchAll();
		}else {
			return false;
		}
	} catch (\Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
	}
}

//================================
// その他
//================================

//クラス付与
function appendClass($key) {
	global $err_msg;
	if(!empty($err_msg[$key])) {
		return 'u-err';
	}
}
//サニタイズ
function sanitize($str) {
	return htmlspecialchars($str, ENT_QUOTES);
}
//フォーム入力保持
function getFormData($key, $flg = false) {
	if($flg) {
		$method = $_GET;
	}else {
		$method = $_POST;
	}
	global $dbFormData,
	       $err_msg;
	//ユーザー情報がある場合
	if(!empty($dbFormData)) {
		//フォームのエラーがある場合
		if(!empty($err_msg[$key])) {
			//POSTがある場合
			if(isset($method[$key])) {
				return sanitize($method[$key]);
			}else {
				return sanitize($dbFormData[$key]);
			}
		}else {
			//POSTにデータがあり、DBの情報と違う場合 = 他のフォームでエラーあり
			if(isset($method[$key]) && $method[$key] !== $dbFormData[$key]) {
				return sanitize($method[$key]);
			}else {//POSTにデータがない or あるが、DBと同じ = 何も変更していない
				return sanitize($dbFormData[$key]);
			}
		}
	}else {
		if(isset($method[$key])) {
			return sanitize($method[$key]);
		}
	}
}
//画像処理
function uploadImg($file,$key) {
	global $err_msg;
	debug('画像アップロード処理開始');
	//例外処理
	try {
		//バリデーション
		if(!isset($file['error']) || !is_int($file['error'])) {
			throw new RuntimeException('パラメータが不正です');
		}
		switch ($file['error']) {
			case UPLOAD_ERR_OK: // OK
            break;
        case UPLOAD_ERR_NO_FILE:   // ファイル未選択
            throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過 (設定した場合のみ)
            throw new RuntimeException('ファイルサイズが大きすぎます');
        default:
            throw new RuntimeException('その他のエラーが発生しました');
		}
		//MIMEタイプ取得・確認
		$type = @exif_imagetype($file['tmp_name']);
		if(!in_array($type, [	IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
			throw new RuntimeException('ファイル形式が未対応です');
		}
		
		//ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
		$path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
		if(!move_uploaded_file($file['tmp_name'], $path)) {
			throw new RuntimeException('ファイル保存時にエラーが発生しました');
		}
		//ファイルパスのパーミッションを変更
		chmod($path, 0644);
		
		debug('ファイルは正常にアップロードされました');
		debug('画像パス：'.$path);
		
		return $path;
		
	} catch (\RuntimeException $e) {
		error_log('エラー発生：'.$e->getMessage());
		$err_msg[$key] = $e->getMessage();
	}
}
//sessionのメッセージを一度のみ取得可能
function getSessionFlash($str) {
	if(!empty($_SESSION[$str])) {
		$data = $_SESSION[$str];
		$_SESSION[$str] = '';
		return $data;
	}
}
//メール送信
function sendMail($to,$subject,$comment,$from) {
	if(!empty($to) && !empty($subject) && !empty($comment)) {
		mb_language("Japanese");
		mb_internal_encoding("UTF-8");
		
		if(mb_send_mail($to, $subject, $comment, $from)) {
			debug('メールを送信しました');
		}else {
			debug('エラー発生：メールの送信に失敗しました');
		}
	}
}
//ランダムな文字列を生成
function makeRandKey($length = 8) {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
	$str = '';
	for ($i = 0; $i < $length; $i++) {
		$str .= $chars[mt_rand(0,61)];
		debug('auth_key:'.$str);
	}
	return $str;
}
//画像表示
function showImg($path) {
	if(empty($path)) {
		return './img/sample-img.png';
	}else {
		return $path;
	}
}
//GETパラメータ付与
function appendGetParam($arr_del_key = array()) {
	if(!empty($_GET)) {
		$str = '?';
		foreach ($_GET as $key => $val) {
			if(!in_array($key,$arr_del_key,true)) {
				$str .= $key.'='.$val.'&';
			}
		}
		$str = mb_substr($str, 0, -1, 'UTF-8');
		// debug('$str：'.print_r($str,true));
		return $str;
	}
}