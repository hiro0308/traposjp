$(function() {
	//ハンバーガーメニュー
	$('.js-toggle-menu').on('click', function() {
		$(this).toggleClass('active');
		$('.js-toggle-menu-sp').toggleClass('active');
	});
	
	$('.js-toggle-menu-sp a').on('click', function() {
		$('.js-toggle-menu').trigger('click');
	});
	
	$('.js-toggle-menu-sp').on('click', function() {
		$('.js-toggle-menu').trigger('click');
	});
	
	//画像切り替え
	$('.js-click-img-sub').on('click', function() {
		$('.js-click-img-main').attr('src', $(this).attr('src'));
	});
	
	//タブ切り替え
  $('.js-tab-switch').click(function() {
	
    //クリックされたタブが何番目かを調べ、変数に代入
    var index = $('.js-tab-switch').index(this);
	
    //コンテンツを一度すべて非表示、
    $('.js-toggle-content').css('display','none');
	
    //クリックされたタブと同じ順番のコンテンツを表示
    $('.js-toggle-content').eq(index).css('display','block');
	
    //一度タブについているクラスselectを削除
    $('.js-tab-switch').removeClass('active');
	
    //クリックされたタブのみにクラスselectを付与
    $(this).addClass('active');
  });
	
	
	//文字数カウント
	$('.js-count-target').on('keyup', function() {
		$('.js-count-view').text($(this).val().length);
	});
	
	//画像ライブプレビュー
	var $dropArea = $('.js-drop-area');
	var $fileInput = $('.js-inputFile');
	
	$dropArea.on('dragover', function() {
		$(this).css('border', '3px dotted #ccc');
	});
	$dropArea.on('dragleave', function() {
		$(this).css('border', 'none');
	});
	$fileInput.on('change', function() {
		$($dropArea).css('border', 'none');
	
		var $img = $(this).siblings('.js-set-img'),
		    fileReader = new FileReader(),
				file = this.files[0];
		//画像の読み込み完了時,imgタグのsrcにセット
		fileReader.onload = function(event) {
			$img.attr('src', event.target.result).show();
		};
		//画像読み込み
		fileReader.readAsDataURL(file);
	});
	
	//メッセージ表示
	var $jsShowMsg = $('#js-show-msg'),
			msg = $jsShowMsg.text();
	
	if(msg.replace(/^[\s　]+|[\s　]+$/g, '').length) {
		$jsShowMsg.slideToggle('slow');
		setTimeout(function() {
			$jsShowMsg.slideToggle('slow');
		},5000);
	}
	
	//お気に入り登録
	var $fav = $('.js-click-fav') || null,
			favPostId = $fav.data('postid') || null;
			
	if(favPostId !== undefined && favPostId !== null) {
		$fav.on('click', function() {
			var $this = $(this);
			$.ajax({
				type: "POST",
				url: "ajaxFav.php",
				dataType: 'json',
				data: { postId: favPostId }
			}).done(function(data, status) {
				// console.log('Ajax success');
				// console.log(data);
				$this.toggleClass('active');
				$('.js-fav').text(data['data']);
			}).fail(function() {
				// console.log('Ajax error');
			});
		});
	}
	
	// 掲示板用バリデーション
	if($('.js-submit-form').val() ==　'') {
		$('.js-click-submit').attr('disabled', true);
	}
	$('.js-submit-form').on('keyup', function() {
		
		if($(this).val().length > 255) {
			$('.js-click-submit').attr('disabled', true);
		
		}else if($('.js-submit-form').val() ==　''){
			$('.js-click-submit').attr('disabled', true);
		
		}else {
			$('.js-valid-msg').text('');
			$('.js-click-submit').attr('disabled', false);
		}
	});
	
	// メッセージ送信(非同期)(掲示板)
	var $submit = $('.js-click-submit') || null,
			boardId = $submit.data('boardid') || null;
				
	if(boardId !== undefined && boardId !== null) {
		$submit.on('click', function() {
			var msg = $('.js-submit-form').val();
			console.log(msg);
			var $this = $(this);
			$.ajax({
				type: "POST",
				url: "ajaxSubmit.php",
				dataType: "json",
				data: {
					 msg: msg,
					 boardId: boardId
				 }
			}).done(function(data, status) {
				//フォームの中身を削除
				$('.js-submit-form').val('');
				//メッセージを追加
				$('.js-add-msg').append(data.data);
				//掲示板スクロール
				$('.js-scroll-bottom').animate({scrollTop: $('.js-scroll-bottom')[0].scrollHeight}, 'fast');
			}).fail(function() {
			});
		});
	}
});