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
								<tr>
                                    <td class="white">Completed</td>
                                    <td><?=number_format($level->count);?></td>
                                </tr>
<?php if (isset($level->last_completed)): ?>
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
							</tr>
							</tbody>
						</table>
<?php
    if (isset($currentLevel->data['hint']) && (!isset($level->attempt) || $level->attempt !== true)):
?>
                        <div class='level-hint'>
                            <a class='left button' href='#'>Show hint</a>
                        </div>
<?php
    endif;
?>
                    </article>