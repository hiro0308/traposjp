<aside class="l-sidebar">
	<form action="" class="c-form p-search" method="get">
		<div class="p-search__body">
			<h2 class="p-search__title">条件で絞り込む</h2>
			<div class="p-search__container">
				<input type="text" name="search" value="<?php echo getFormData('search', true); ?>"
							 placeholder="キーワードで検索" class="p-search__text"
				>
				<i class="fas fa-search p-search--icon"></i>
			</div>
			
			<h2 class="p-search__title">都道府県</h2>
			<select name="c_id" class="p-search__selectBox">
				<option value="0" <?php if(getFormData('c_id',true) == 0 ){ echo 'selected'; } ?> >エリアを選択</option>
				<?php foreach($dbCategoryData as $key => $val): ?>
					<option value="<?php echo $val['id'] ?>" <?php if(getFormData('c_id',true) == $val['id'] ){ echo 'selected'; } ?> >
						<?php echo $val['name']; ?>
					</option>
				<?php endforeach; ?>
			</select>
			
			<h2 class="p-search__title">日付順</h2>
			<select name="sort" class="p-search__selectBox">
				<option value="0" <?php if(getFormData('sort',true) == 0 ){ echo 'selected'; } ?> >並びを選択</option>
				<option value="1" <?php if(getFormData('sort',true) == 1 ){ echo 'selected'; } ?> >投稿日が新しい順</option>
				<option value="2" <?php if(getFormData('sort',true) == 2 ){ echo 'selected'; } ?> >投稿日が古い順</option>
			</select>
			
			<input type="submit" class="p-search__submit" value="検索">
		</div>
	</form>
</aside>