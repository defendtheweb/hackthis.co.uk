<?php
    $custom_css = array('faq.scss');
    require_once('header.php');

    $donations = new donations($app);
?>
    <h1>Become a Donator</h1>
    In order to support our growth and the costs of maintaining and developing new features for our color loving community, we've added some great extended features and are offering them as a thank you to those who support us with a small donation:<br/>
    <p>
        <h2 class='no-margin'>£1 or more</h2>
        Get yourself listed on our donator hall of fame.
    </p>
    <p>
        <h2 class='no-margin'>£5 or more</h2>
        You will not only be listed on our donator hall of fame, you will also be awarded the donator medal. On top
        of this a heart will be displayed next to your username throughout the site to show your support.
    </p>
    <p>
        <h2 class='no-margin'>£20 or more</h2>
        As well as all perks listed above you will also be able to show off your support with a stylish HackThis!! T-shirt.
    </p>
    <br/>
    <p>
        <h2 class='no-margin'>Recent Donations</h2>
        <table class="striped sortable">
            <thead>
                <tr>
                    <th width="60%">Username</th>
                    <th>Amount</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
<?php
    $donators = $donations->getAll();
    foreach($donators as $donation):
?>
                <tr>
                    <td><?=$app->utils->userLink($donation->username);?></td>
                    <td>£<?=$donation->amount;?></td>
                    <td><time datetime="<?=date('c', strtotime($donation->time));?>"><?=$app->utils->timeSince($donation->time);?></time></td>
                </tr>
<?php
    endforeach;
?>
            </tbody>
        </table>
    </p>

<?php  
    require_once('footer.php');
?>