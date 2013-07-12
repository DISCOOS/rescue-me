<form class="form-user" method="post">
    <h3 ><?=NEW_USER?></h3>
    <?php
        if(isset($_ROUTER['message'])) { 
    ?>
        <div class="alert alert-error"><?= $_ROUTER['message'] ?></div>
    <?
        } 
    ?>    
    <input type="text" name="name" class="input-block-level" placeholder="Fullt navn">
    <input type="email" name="username" class="input-block-level" placeholder="E-postadresse">
    <input type="password" name="password" class="input-block-level" placeholder="Passord">
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><?= CREATE ?></button>
        <a type="button" class="btn" href="javascript: history.go(-1)"><?= CANCEL ?></a>
    </div>    
</form>