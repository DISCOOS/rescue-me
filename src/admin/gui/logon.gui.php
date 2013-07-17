<form class="form-signin" method="post" action="<?= ADMIN_URI."logon" ?>">
    <h2 class="form-signin-heading">Logg inn</h2>
    <?php
        if(isset($_ROUTER['message'])) { 
    ?>
        <div class="alert alert-error"><?= $_ROUTER['message'] ?></div>
        <input type="email" name="username" class="input-block-level alert-error" placeholder="E-postadresse">
        <input type="password" name="password" class="input-block-level alert-error" placeholder="Passord">
    <?
        } else {
    ?>    
        <input type="email" name="username" class="input-block-level" placeholder="E-postadresse"></input>
        <input type="password" name="password" id="password" class="input-block-level" placeholder="Passord" data-content="Cap-lock is on!"></input>
    <?
        }
    ?>    
    <label class="checkbox">
        <input type="checkbox" value="remember-me"> Husk meg på denne maskinen
    </label>
    <button class="btn btn-large btn-primary" type="submit">Logg inn</button>
</form>