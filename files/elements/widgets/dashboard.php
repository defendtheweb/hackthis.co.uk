                    <article class="widget">
                        <h1><a href='/user/<?=$user->username;?>'><?=$user->username;?></a></h1>
<?php
    if ($user->connect_msg):
?>
                        <div class='msg msg-info'>
                            <i class='icon-info'></i>
                            <?=$user->connect_msg;?>
                        </div>
<?php
    endif;
    if (!$user->connected):
?>
                        <a class='stop-external facebook-connect' href='https://www.facebook.com/dialog/oauth?client_id=<?php $fb = $app->config('facebook'); echo $fb['public'];?>&redirect_uri=http://dev.hackthis/?facebook&scope=email'>
                            Connect to Facebook
                        </a>
<?php
    endif;
?>
                        Score: <?=$user->score;?>
                    </article>