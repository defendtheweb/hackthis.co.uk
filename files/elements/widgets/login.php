                    <article class="widget widget-login">
<!--                         <h1>Login</h1> -->
<?php
    if (isset($app->user->login_error)):
?>
                        <div class='msg msg-error'>
                            <i class='icon-error'></i>
                            <?=$app->user->login_error;?>
                        </div>
<?php
    endif;
?>
                        <form id="login_form" action="?login" method="POST">
                            <label for="username">Username:</label>
                            <input type="text" name="username" id="username">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password">
                            <a class="white" href="/?request">Forgot details?</a>
                            <input type="submit" value="Login" class="button">
                        </form>

                        <a class='stop-external facebook-login' href='https://www.facebook.com/dialog/oauth?client_id=<?php $fb = $app->config('facebook'); echo $fb['public'];?>&redirect_uri=http://dev.hackthis/?facebook&scope=email'>
                            Login with Facebook
                        </a>
                        <a class='stop-external twitter-login' href='#'>
                            Login with Twitter
                        </a>
                    </article>