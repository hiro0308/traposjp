<header class="l-header">
<div class="l-header__inner l-container">
	<!-- サイトタイトル -->
	<h1 class="l-header__title"><a href="index.php">trapos</a><span class="l-header__subTitle">travel diary</span></h1>
	<!-- スマートフォン用メニュー -->
	<div class="u-trigger js-toggle-menu">
		<span class="u-trigger--line"></span>
		<span class="u-trigger--line"></span>
		<span class="u-trigger--line"></span>
	</div>
	
	<nav class="c-nav js-toggle-menu-sp">
		<ul class="c-nav__list">
			<?php if(empty($_SESSION['user_id'])): ?>
				<li class="c-nav__item"><a href="index.php" class="c-nav__link">TOP</a></li>
				<li class="c-nav__item"><a href="login.php" class="c-nav__link">ログイン</a></li>
				<li class="c-nav__item"><a href="signup.php" class="c-nav__link c-nav__link--signup">ユーザー登録</a></li>
			<?php else: ?>
				<li class="c-nav__item"><a href="mypage.php" class="c-nav__link">マイページ</a></li>
				<li class="c-nav__item"><a href="logout.php" class="c-nav__link">ログアウト</a></li>
			<?php endif; ?>
		</ul>
	</nav>
</div>
</header>