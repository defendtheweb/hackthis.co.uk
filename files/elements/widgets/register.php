                    <article class="widget">
                        <h1>Registration</h1>
<?php
    if (isset($user->reg_error)):
?>
                        <div class='msg msg-error'>
                            <i class='icon-error'></i>
                            <?=$user->reg_error;?>
                        </div>
<?php
    endif;
?>
                        <form id="registration_form" action="?register" method="POST">
                            <label>Username:</label>
                            <input type="text" name="reg_username" id="reg_username" autocomplete="off" value="<?=htmlspecialchars($_POST['reg_username'])?>">
                            <label>Password:</label>
                            <input type="password" name="reg_password" id="reg_password" autocomplete="off">
                            <label>Repeat Password:</label>
                            <input type="password" name="reg_password_2" id="reg_password_2" autocomplete="off">
                            <label>Email:</label>
                            <input type="text" name="reg_email" id="reg_email" value="<?=htmlspecialchars($_POST['reg_email'])?>">
                            <input type="submit" value="Register" class="button right">
                        </form>
                    </article>
