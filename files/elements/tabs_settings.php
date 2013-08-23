    <ul class='tabs'>
        <li <?=$tab=='profile'?"class='active'":'';?>><a href='/settings/'>Profile</a></li>
        <li <?=$tab=='image'?"class='active'":'';?>><a href='/settings/image.php'>Image</a></li>
        <li <?=$tab=='friends'?"class='active'":'';?>><a href='/settings/friends.php'>Friends</a></li>
        <li <?=$tab=='security'?"class='active'":'';?>><a href='/settings/security.php'>Security</a></li>
        <li <?=$tab=='notifications'?"class='active'":'';?>><a href='/settings/notifications.php'>Notifications</a></li>
        <li class='right <?=$tab=='account'?'active':'';?>'><a href='/settings/account.php'>Account</a></li>
    </ul>