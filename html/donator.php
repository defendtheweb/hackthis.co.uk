<?php
    $custom_css = array('faq.scss');
    $custom_js = array('faq.js');
    require_once('init.php');

    $donations = new donations($app);

    if (isset($_POST['donate'])) {
        $amount = floatval(ltrim($_POST['donate'],"£"));

        $_SESSION['donate_anon'] = (isset($_POST['anon']) && $_POST['anon']);

        if ($amount && $amount > 0 && $amount < 10000)
            $donations->makeTransaction($amount, $_POST['size']);
    }

    require_once('header.php');

    if (isset($_GET['token']) && isset($_GET['PayerID'])) {
        $status = $donations->confirmPayment($_GET['token'], $_GET['PayerID']);
        if ($status) {
            $app->utils->message('Thank you for your donation, you are awesome!', 'good');
        } else {
            $app->utils->message('Something went wrong');
        }
    }
?>
    <h1>Become a Donator</h1>
    <?php $app->utils->message('Shirts are currently not available, if you still wish to order one please contact us', 'info'); ?>
    <p>In order to support our growth and the costs of maintaining and developing new features, we've added some perks and are offering them as a thank you to those who support us with a small donation:<br/>
    <?php if (isset($_GET["currency"]) == "eur") { ?>
        Select your currency:
        <select onChange="window.location.href=this.value" style="max-width: 100px;">
            <option value="?currency=gbp">Great British Pounds</option>
            <option selected>Euro</option>
            <option value="?currency=usd">US Dollars</option>
        </select><br />
        <p>
            <h2 class='no-margin'>€1.50 or more</h2>
            Get yourself listed on our donator hall of fame.
        </p>
        <p>
            <h2 class='no-margin'>€7 or more</h2>
            You will not only be listed on our donator hall of fame, you will also be awarded the donator medal. On top
            of this a heart will be displayed next to your username throughout the site to show your support.
        </p>
        <p>
            <h2 class='no-margin'>€21 or more</h2>
            As well as all perks listed above you will also be able to show off your support with a stylish HackThis!! T-shirt.
        </p>
    <?php } elseif (isset($_GET["currency"]) == "usd") { ?>
        Select your currency:
        <select onChange="window.location.href=this.value" style="max-width: 100px;">
            <option value="?currency=gbp">Great British Pounds</option>
            <option value="?currency=eur">Euro</option>
            <option selected>US Dollars</option>
        </select><br />
        <p>
            <h2 class='no-margin'>$1.50 or more</h2>
            Get yourself listed on our donator hall of fame.
        </p>
        <p>
            <h2 class='no-margin'>$7.50 or more</h2>
            You will not only be listed on our donator hall of fame, you will also be awarded the donator medal. On top
            of this a heart will be displayed next to your username throughout the site to show your support.
        </p>
        <p>
            <h2 class='no-margin'>$22 or more</h2>
            As well as all perks listed above you will also be able to show off your support with a stylish HackThis!! T-shirt.
        </p>
    <?php } else { ?>
        Select your currency:
        <select onChange="window.location.href=this.value" style="max-width: 100px;">
            <option selected>Great British Pounds</option>
            <option value="?currency=eur">Euro</option>
            <option value="?currency=usd">US Dollars</option>
        </select><br />
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
            <h2 class='no-margin'>£15 or more</h2>
            As well as all perks listed above you will also be able to show off your support with a stylish HackThis!! T-shirt.
        </p>
    <?php } ?>
    <br/>
    <form method="POST" class='center donate'>
        <input name="donate" class='tiny' placeholder="Amount to donate in £"/><br/>
        <div class='donate-perk hide'>
            <label style="display: inline-block; width: 25%; min-width: 160px; text-align: left; margin-top: 0; margin-bottom: 4px" for="size">Shirt size</label><br/>
            <select class='tiny' id="size" name="size">
                <option value="s">Small</option> 
                <option value="m" selected="selected">Medium</option>
                <option value="l">Large</option>
                <option value="xl">XLarge</option>
                <option value="xxl">XXLarge</option>
            </select>
        </div>
        <input type="submit" value="Donate" class="button"/><Br/>
        <input type="checkbox" id="anon" name="anon"/>
        <label for="anon">donate anonymously</label>
    </form>

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
                    <td><?=($donation->username)?$app->utils->userLink($donation->username):'Anonymous';?></td>
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
