<?php
    $custom_js = array('profile.js');
    $custom_css = array('settings.scss', 'profile.scss');
    require_once('init.php');

    $app->page->title = 'Settings - Friends';

    $profile = new profile($app->user->username);

    require_once('header.php');

    $tab = 'friends';
    include('elements/tabs_settings.php');
?>

    <div id="friends-search" class="mobile-hide">
        <input placeholder="Search friends">
        <i class="icon-search"></i>
    </div>
    <h1>Friends <?=count($profile->friendsList)?'['.count($profile->friendsList).']':'';?></h1>

<?php
    if (!count($profile->friendsList)):
        $app->utils->message('You haven\'t added any friends yet', 'info');
    else:
?>
        <ul class='users-list'>
<?php
        foreach($profile->friendsList as $friend):
            if (isset($friend->image)) {
                $gravatar = isset($friend->gravatar) && $friend->gravatar == 1;
                $friend->image = profile::getImg($friend->image, 48, $gravatar);
            } else
                $friend->image = profile::getImg(null, 48);
?>
            <li>
                <div>
                    <a href='/user/<?=$friend->username;?>'>
                        <img src='<?=$friend->image;?>' width='100%' alt='<?=$friend->username;?> profile picture'/>
                    </a>
                    <div>
                        <span><a href='/user/<?=$friend->username;?>'><?=$friend->username;?></a></span><br/>
                        <span class='icons'>
                            <a href='/inbox/compose?to=<?=$friend->username;?>' title='PM user' class='messages-new' data-to="<?=$friend->username;?>"><i class='icon-envelope-alt'></i></a>&nbsp;&nbsp;
                            <a href='#' class='removefriend removefriend-hide' title='Delete user' data-uid='<?=$friend->uid;?>'><i class='icon-cross'></i></a>
                        </span>
                    </div>
                </div>
            </li>
<?php   endforeach; ?>
        </ul>
<?php
    endif;

    require_once('footer.php');
?>