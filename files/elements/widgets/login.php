                    <article class="widget">
                        <h1>Login</h1>
<?php
    if (isset($user->login_error)):
?>
                        <div class='msg msg-error'>
                            <i class='icon-error'></i>
                            <?=$user->login_error;?>
                        </div>
<?php
    endif;
?>
                        <form id="login_form" action="?login" method="POST">
                            <label>Username:</label>
                            <input type="text" name="username" id="username">
                            <label>Password:</label>
                            <input type="password" name="password" id="password">
                            <span class="right">
                                <a class="white" href="/?request">Request Details</a> <input type="submit" value="Login" class="button">
                            </span>
                        </form>
                    </article>