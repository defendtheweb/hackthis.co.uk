        <div id="global-nav">
            <nav class="container row">
                <ul>
                    <li><a href='/'>home</a></li>
<?php
    if ($user->loggedIn) {
?>
                    <li><a href='/'>levels</a>
                        <ul>
                            <li><a href='/'>Main <i class='icon-caret-right'></i></a>
                                <ul>
                                    <li><a href='/'>Level 1</a></li>
                                    <li><a href='/'>Level 2</a></li>
                                    <li><a href='/'>Level 3</a></li>
                                </ul>
                            </li>
                            <li><a href='/'>Basic+ <i class='icon-caret-right'></i></a>
                                <ul>
                                    <li><a href='/'>Level 1</a></li>
                                </ul>
                            </li>
                            <li><a href='/'>Javascript <i class='icon-caret-right'></i></a></li>
                        </ul>
                    </li>
<?php
    }
?>
                    <li><a href='/'>articles</a></li>
                    <li><a href='/'>forum</a></li>
                    <li><a href='/'>irc</a>
                        <ul>
                            <li><a href='/'>Chat Online</a></li>
                            <li><a href='/'>stats</a></li>
                            <li><a href='/'>about</a></li>
                        </ul>
                    </li>
                    <li><a href='/'>contact us</a></li>

<?php
    if ($user->loggedIn) {
?>
                    <li class='right mobile-hide'><a href='/'><i class="icon-tasks"></i></a>
                        <ul>
                            <li><a href='/settings/'>Settings</a></li>
                            <li class='seperator'><a href='/?logout'>Logout</a></li>
                        </ul>
                    </li>
                    <li class='mobile-only'><a href='/settings/'>Settings</a></li>
                    <li class='mobile-only'><a href='/?logout'>Logout</a></li>
                    <li class='right'><a class='nav-extra nav-extra-pm' href='/inbox/'><i class="icon-envelope-alt"></i><span class='notification-counter' id='pm-counter'>1</span></a></li>
                    <li class='right nav-extra-events-cont'><a class='nav-extra nav-extra-events' href='/alerts.php'><i class="icon-globe"></i><span class='notification-counter' id='event-counter'>1</span></a></li>
                    <li class='right mobile-hide'><a href='/'><i class="icon-search"></i></a></li>
<?php
    }
?>
                </ul>
                <div id='nav-extra-dropdown'>
                    Hello I am some extra information
                </div>
            </nav>
        </div>