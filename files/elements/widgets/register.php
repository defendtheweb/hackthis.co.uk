                    <article class="widget widget-register">
                        <!-- <h1>Registration</h1> -->
<?php
    if (isset($app->user->reg_error)):
?>
                        <div class='msg msg-error'>
                            <i class='icon-error'></i>
                            <?=$app->user->reg_error;?>
                        </div>
<?php
    endif;
?>
                        <form id="registration_form" action="?register" method="POST">
                            <span class='hint--left right' data-hint="Username must be longer than 3 characters&#10;Only contain the characters; a-z A-Z _ . -"><i class='icon-info'></i></span>
                            <label for="reg_username">Username:</label>
                            <input type="text" name="reg_username" id="reg_username" autocomplete="off" value="<?=isset($_POST['reg_username'])?htmlspecialchars($_POST['reg_username']):''?>">
                            <label for="reg_email">Email:</label>
                            <input type="text" name="reg_email" id="reg_email" value="<?=isset($_POST['reg_email'])?htmlspecialchars($_POST['reg_email']):''?>">
                            <div class='hide' id="reg_email_suggestion">Suggestion</div>
                            <input type="text" name="reg_email_2" id="reg_email_2" autocomplete="off" value="">
                            <label for="reg_password">Password:</label>
                            <input type="password" name="reg_password" id="reg_password" autocomplete="off">
                            <label for="reg_password_2">Repeat Password:</label>
                            <input type="password" name="reg_password_2" id="reg_password_2" autocomplete="off">

                            <div class='small'>By providing my information and clicking on the register button, I confirm that I have read and agree to this website's terms and conditions and privacy policy.</div><br/>
                            <input type="submit" value="Register" class="button right clr">
                        </form>

                        <a class='stop-external facebook-login' href='https://www.facebook.com/dialog/oauth?client_id=<?php $fb = $app->config('facebook'); echo urlencode($fb['public']);?>&amp;redirect_uri=<?=urlencode($app->config('domain'));?>/?facebook&amp;scope=email'>
                            Login with Facebook
                        </a>
                    </article>