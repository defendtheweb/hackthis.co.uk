                    <article class="widget widget-login">
                        <!-- <h1>Login</h1> -->
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
                        <script>
                            // We would like to set focus to the username element only if this is a
                            // returning user, meaning that he already logged in in the past.
                            // If he's not, on smaller screens the screen will scroll down and new
                            // users won't see the intro text.
                            // Returning users are marked by the key ht_returning_user in local
                            // stroage upon login.

                            if (Modernizr.localstorage && window.localStorage['ht_returning_user'] === 'true') {
                                $( document).ready(function() {
                                    $("#username").focus();
                                });
                            }
                        </script>

                        <form id="login_form" action="?login" method="POST">
                            <label for="username">Username:</label>
                            <input type="text" name="username" id="username">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password">
                            <a class="white" href="/?request">Forgot details?</a>
                            <input type="submit" value="Login" class="button">
                        </form>

                        <a class='stop-external facebook-login' href='https://www.facebook.com/dialog/oauth?client_id=<?php $fb = $app->config('facebook'); echo urlencode($fb['public']);?>&amp;redirect_uri=<?=urlencode($app->config('domain'));?>/?facebook&amp;scope=email'>
                            Login with Facebook
                        </a>
                    </article>
