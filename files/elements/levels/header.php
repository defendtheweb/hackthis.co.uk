<div class='level-header center'>
<?php if (isset($level->level_before_uri)): ?>
	<a class='left previous-level' href='<?=$level->level_before_uri;?>'><i class='icon-caret-left'></i></a>
<?php else: ?>
	<span class='left previous-level dark'><i class='icon-caret-left'></i></span>
<?php 
	endif;
	if (isset($level->level_after_uri) && ($level->group != 'main' || $level->completed)):
?>
	<a class='right next-level' href='<?=$level->level_after_uri;?>'><i class='icon-caret-right'></i></a>
<?php elseif(isset($level->level_after_uri) && $level->group == 'main'): ?>
	<span class='right next-level dark hint--left' data-hint="You must complete main levels in order,&#10;but you can attempt any other level."><i class='icon-caret-right'></i></span>
<?php else: ?>
	<span class='right next-level dark'><i class='icon-caret-right'></i></span>
<?php endif; ?>

	<h1 class='no-margin'><?=$level->title;?></h1>
<?php if (isset($level->data['author'])): ?>
	created by <a href='/user/<?=$level->data['author'];?>'><?=$level->data['author'];?></a><br/>
<?php endif; ?>
	<span class='strong <?=$level->completed?'green':'red';?>'><?=$level->completed?'Completed':'Incomplete';?></span>
</div>