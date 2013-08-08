        <div id="global-nav-sticky">
            <div id="global-nav">
                <nav class="container row">
                    <a class='mobile-only show-nav' href='#'><i class="icon-menu"></i></a>
                    <ul>
                        <li><a href='/'>home</a></li>
<?php
    if ($user->loggedIn):
?>
                        <li><a href='/'>levels</a>
                            <ul>
                                <li class='parent'><a href='/'>Main</a>
                                    <ul>
                                        <li><a href='/'>Level 1</a></li>
                                        <li><a href='/'>Level 2</a></li>
                                        <li><a href='/'>Level 3</a></li>
                                    </ul>
                                </li>
                                <li class='parent'><a href='/'>Basic+</a>
                                    <ul>
                                        <li><a href='/'>Level 1</a></li>
                                    </ul>
                                </li>
                                <li><a href='/'>Javascript</a></li>
                            </ul>
                        </li>
<?php
    else:
?>
                        <li><a href='/news/'>news</a></li>
<?php
    endif;

    $categories = articles::getCategories(null, false);
?>
                        <li><a href='/articles/'>articles</a>
                            <ul>
                                <li class='parent'><a href='/articles/'>categories</a>
                                    <ul>
<?php
    foreach($categories as $cat) {
        articles::printCategoryList($cat, true);
    }
?>
                                    </ul>
                                </li>
<?php
    if ($user->loggedIn):
?>
                                <li class='parent'><a href='/articles/me'>My Articles</a>
                                    <ul>
                                        <li><a href='/articles/me'>Approved</a></li>
                                        <li><a href='/articles/me?submissions'>Submitted</a></li>
                                        <li><a href='/articles/submit'>Submit</a></li>
                                    </ul>
                                </li>
<?php
    endif;
?>
                            </ul>
                        </li>
                        <li><a href='/forum/'>forum</a></li>
                        <li><a href='/irc/'>irc</a>
                            <ul>
                                <li><a href='/'>Chat Online</a></li>
                                <li><a href='/'>stats</a></li>
                                <li><a href='/'>about</a></li>
                            </ul>
                        </li>
                        <li><a href='/'>contact us</a></li>

<?php
    if ($user->loggedIn):
?>
                        <li class='right icon mobile-hide'><a href='/'><i class="icon-menu"></i></a>
                            <ul>
                                <li><a href='/settings/'>Settings</a></li>
                                <li class='seperator'><a href='/?logout'>Logout</a></li>
                            </ul>
                        </li>
                        <li class='mobile-only'><a href='/settings/'>Settings</a></li>
                        <li class='mobile-only'><a href='/?logout'>Logout</a></li>
<?php
        if ($user->admin):
?>
                        <li class='right icon mobile-hide'><a href='/admin/'><i class="icon-lock"></i></a>
                            <ul>
<?php
            if ($user->admin_site_priv):
?>
                                <li><a href='/admin/levels.php'>Levels</a></li>
                                <li><a href='/admin/users.php'>Users</a></li>
<?php
            endif;
            if ($user->admin_pub_priv):
?>
                                <li><a href='/admin/articles.php'>Articles</a></li>
<?php
            endif;
            if ($user->admin_forum_priv):
?>
                                <li><a href='/admin/forum.php'>Forum</a></li>
<?php
            endif;
?>
                                <li><a href='/admin/tickets.php'>Tickets</a></li>
                                <li><a href='/admin/misc.php'>Misc</a></li>
                            </ul>
                        </li>
<?php
        endif;
?>
                        <li class='right icon'><a class='nav-extra nav-extra-pm' href='/inbox/'><i class="icon-envelope-alt"></i><span class='notification-counter' id='pm-counter'>1</span></a></li>
                        <li class='right icon nav-extra-events-cont'><a class='nav-extra nav-extra-events' href='/alerts.php'><i class="icon-globe"></i><span class='notification-counter' id='event-counter'>1</span></a></li>
                        <li class='right icon mobile-hide nav-search'>
                            <form action='/search.php' method='get'>
                                <input placeholder='Search: topic, user, level..' name='q'/>
                                <a href='#' onclick="$(this).parent().submit();"><i class='icon-search'></i></a>
                            </form>
                        </li>
<?php
    elseif (defined('_SIDEBAR') && !_SIDEBAR): // right, if not logged in
?>
                        <li class='right nav-extra-login-item <?=isset($user->login_error)?'active':'';?>'><a class='nav-extra nav-extra-login' href='/' <?=(isset($user->login_error))?"data-error='{$user->login_error}'":'';?>>Login</a></li>
                        <li class='right nav-extra-register-item <?=isset($user->reg_error)?'active':'';?>'><a class='nav-extra nav-extra-register' href='/' <?=(isset($user->reg_error))?"data-error='{$user->reg_error}'":'';?>>Register</a></li>
<?php
    endif;
?>
                    </ul>
<?php
    if ($user->loggedIn):
?>
                    <div id='nav-extra-dropdown'>
                        Hey there :)
                    </div>
<?php
    elseif (defined('_SIDEBAR') && !_SIDEBAR): // right, if not logged in
?>
                    <div id='nav-extra-login' class='nav-extra-dropdown'>
                        <?php include('elements/widgets/login.php'); ?>
                    </div>
                    <div id='nav-extra-register' class='nav-extra-dropdown'>
                        <?php include('elements/widgets/register.php'); ?>
                    </div>
<?php
    endif;
?>
                </nav>
            </div>
        </div>