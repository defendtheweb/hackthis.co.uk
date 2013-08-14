<div class='level-header center'>
<?php if (isset($level->level_before_uri)): ?>
	<a class='left previous-level' href='<?=$level->level_before_uri;?>'><i class='icon-caret-left'></i></a>
<?php else: ?>
	<span class='left  previous-level dark'><i class='icon-caret-left'></i></span>
<?php 
	endif;
	if (isset($level->level_after_uri) && ($level->group != 'main' || $level->completed)):
?>
	<a class='right next-level' href='<?=$level->level_after_uri;?>'><i class='icon-caret-right'></i></a>
<?php else: ?>
	<span class='right next-level dark'><i class='icon-caret-right'></i></span>
<?php endif; ?>

	<h1 class='no-margin'><?=$level->title;?></h1>
	Status: <span class='<?=$level->completed?'green':'red';?>'><?=$level->completed?'Complete':'Incomplete';?></span>
</div>