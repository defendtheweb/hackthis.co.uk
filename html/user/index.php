<?php
    $custom_css = array('profile.scss');
    require_once('header.php');
    $profile = $app->utils->get_profile($_GET['user']);
    //print_r($profile);

    function print_item($key, $value, $time=false) {
        global $app;
        if ($time) {
            $value = '<time datetime="' . date('c', strtotime($value)) . '">' . date('d/m/Y', strtotime($value)) . '</time>';
        } else {
            $value = $app->parse($value, false, false);
        }
        return "                    <li><span class='strong'>{$key}:</span> {$value}</li>\n";
    }
?>
    <article class='profile'>
        <h1><?=$profile->username;?></h1>
        <section class='row fluid'>
            <div class='col span_7 clr'>
                <img src='https://www.hackthis.co.uk/users/images/198/1:1/<?=md5($profile->username);?>.jpg' width='100%' alt='<?=$profile->username;?> profile picture'/><br/>
                <div class='progress-container'><div class='progress' style='width: 90%'>90%</div></div>

                <ul class='medals'>
<?php
    foreach ($profile->medals as $medal):
?>
                <li class="medal medal-<?=$medal->colour;?>"><?=$medal->label;?></li>
<?php
    endforeach;
?>
                </ul>
            </div>
            <div class='col span_17 clr'>
                <ul>
<?php
    if ($profile->name) { echo print_item("Name", $profile->name); }
    if ($profile->name) { echo print_item("Gender", ucfirst($profile->gender)); }

    echo print_item("Joined", $profile->joined, true);
    echo print_item("Last seen", $profile->last_active, true);
?>
                    <li><ul class='social'>
                        <li><a class='hide-external' href='http://www.hackthis.co.uk'><i class='icon-globe'></i></a></li>
                        <li><a href='/'><i class='icon-envelope-alt'></i></a></li>
                        <li><a href='/'><i class='icon-github-2'></i></a></li>
                        <li><a href='/'><i class='icon-steam'></i></a></li>
                        <li><a href='/'><i class='icon-twitter'></i></a></li>
                        <li><a href='/'><i class='icon-facebook'></i></a></li>
                        <li><a href='/'><i class='icon-lastfm'></i></a></li>
                        <li><a href='/'><i class='icon-google-plus'></i></a></li>
                        <li><a href='/'><i class='icon-stackoverflow'></i></a></li>
                    </ul></li>
                </ul>
            </div>
        </section>
        <section class='fluid'>
            <ul>
<?php
    foreach($profile->feed as $item):
?>
                <li>
                    <span class='col span_18'><?php print_r($item);?></time></span>
                    <span class='col span_6 text-right'><time datetime="<?=date('c', $item->timestamp);?>"><?=date('d/m/Y', $item->timestamp);?></time></span>
                </li>
<?php
    endforeach;
?>
            </ul>
        </section>
    </article>
<?php
    require_once('footer.php');
?>