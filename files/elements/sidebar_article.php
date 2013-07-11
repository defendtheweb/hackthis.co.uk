<div class="col span_6 article-sidebar">
	<div class='sticky'>
		<a class='button' href='/articles/'><i class='icon-caret-left'></i> Article Index</a>
		<br/><br/>
		<h2>Categories</h2>
            <ul class='categories'>
<?php
                $categories = articles::getCategories(null, false);
                foreach($categories as $cat) {
                    articles::printCategoryList($cat, true, '', ($category)?$category->parent:null);
                }
?>
            </ul>
	</div>
</div>