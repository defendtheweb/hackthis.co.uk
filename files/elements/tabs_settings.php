    <ul class='tabs'>
        <li <?=$tab=='profile'?"class='active'":'';?>><a href='/settings/'>Profile</a></li>
        <li <?=$tab=='image'?"class='active'":'';?>><a href='/settings/image.php'>Image</a></li>
        <li <?=$tab=='friends'?"class='active'":'';?>><a href='/settings/friends.php'>Friends</a></li>
        <li <?=$tab=='forum'?"class='active'":'';?>><a href='/settings/forum.php'>Forum</a></li>
        <li <?=$tab=='notifications'?"class='active'":'';?>><a href='/settings/notifications.php'>Notifications</a></li>
        <li <?=$tab=='userbars'?"class='active'":'';?>><a href='/settings/userbars.php'>Userbars</a></li>
        <li <?=$tab=='2-step'?"class='active'":'';?>><a href='/settings/2-step.php'>2-Step Verification</a></li>
        <li class='right <?=$tab=='account'?'active':'';?>'><a href='/settings/account.php'>Account</a></li>
        <li class='right <?=$tab=='privacy'?'active':'';?>'><a href='/settings/privacy.php'>Privacy</a></li>
    </ul>