                    <article class="widget widget-register">
<!--                         <h1>Registration</h1> -->
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
                            <label for="reg_password">Password:</label>
                            <input type="password" name="reg_password" id="reg_password" autocomplete="off">
                            <label for="reg_password_2">Repeat Password:</label>
                            <input type="password" name="reg_password_2" id="reg_password_2" autocomplete="off">
                            <label for="reg_email">Email:</label>
                            <input type="text" name="reg_email" id="reg_email" value="<?=isset($_POST['reg_email'])?htmlspecialchars($_POST['reg_email']):''?>">
                            <input type="submit" value="Register" class="button right">
                        </form>

                        <a class='stop-external facebook-login' href='https://www.facebook.com/dialog/oauth?client_id=<?php $fb = $app->config('facebook'); echo $fb['public'];?>&redirect_uri=http://dev.hackthis/?facebook&scope=email'>
                            Login with Facebook
                        </a>
                    </article>