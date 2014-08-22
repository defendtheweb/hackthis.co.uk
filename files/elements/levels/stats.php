                   <article>
                        <h1 class='title'>Stats</h1>
						<table>
							<tbody>
<?php if (isset($level->data['author'])): ?>
								<tr>
                                    <td class="white">Author</td>
                                    <td><a href='/user/<?=$level->data['author'];?>'><?=$level->data['author'];?></a>
                                    </td>
                                </tr>
<?php endif; ?>
								<tr>
                                    <td class="white">Reward</td>
                                    <td><?=number_format($level->data['reward']);?> pts</td>
                                </tr>
<?php if (isset($level->count)): ?>
								<tr>
                                    <td class="white">Completed</td>
                                    <td><?=number_format($level->count);?></td>
                                </tr>
<?php
    endif;
    if (isset($level->last_completed)):
?>
								<tr>
                                    <td class="white">Latest</td>
                                    <td><time datetime="<?=date('c', strtotime($level->last_completed));?>"><?=$app->utils->timeSince($level->last_completed);?></time><br>
                                        <a href="/user/<?=$level->last_user;?>"><?=$level->last_user;?></a>
                                    </td>
                                </tr>
<?php
    endif;
    if (isset($level->first_completed)):
?>
								<tr>
                                    <td class="white">First</td>
                                    <td><time datetime="<?=date('c', strtotime($level->first_completed));?>"><?=$app->utils->timeSince($level->first_completed);?></time><br>
                                        <a href="/user/<?=$level->first_user;?>"><?=$level->first_user;?></a>
                                    </td>
                                </tr>
<?php endif; ?>
							</tbody>
						</table>
                    </article>

<?php
    if (!isset($level->attempt) || $level->attempt !== true):
?>
                    <article>
                        <h1 class='title'>Help</h1>
<?php
        if (isset($currentLevel->data['hint'])):
?>
                        <a class='left button level-hint' href='#'>Show hint</a>
<?php
        endif;
?>
                        <a class='left button' href='/forum/level-discussion/<?=$app->utils->generateSlug($currentLevel->group);?>-levels/<?=$app->utils->generateSlug($currentLevel->group);?>-level-<?=$currentLevel->name;?>'>Forum</a>
                    </article>
<?php
    endif;
?>
