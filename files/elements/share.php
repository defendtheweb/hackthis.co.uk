<?php
    array_push($minifier->custom_js, 'share.js');
?>

<ul class='mobile-hide share <?=(isset($share->right)&&$share->right)?'right':'';?>'
  data-id="<?=(isset($share->item))?$share->item:'0';?>"
  data-title="<?=(isset($share->title))?$share->title:'0';?>"
  data-link='<?=(isset($share->link))?$share->link:'0';?>'>
    <li>
        <a class='comments' href='<?=(isset($share->link))?$share->link:'0';?>#comments'>
            <i class='icon-comments'></i>
            <span><?=(isset($share->comments))?$share->comments:'0';?></span>
        </a>
    </li>
    <li>
        <a class='favourite' href='#'>
            <i class='icon-heart<?=(isset($share->favourited) && $share->favourited)?'':'-2';?>'></i>
            <span><?=(isset($share->favourites))?$share->favourites:'0';?></span>
        </a>
    </li>
    <li>
        <a class='facebook' href='#'>
            <i class='icon-facebook-2'></i>
        </a>
    </li>
    <li>
        <a class='twitter' href='#'>
            <i class='icon-twitter-2'></i>
        </a>
    </li>
</ul>