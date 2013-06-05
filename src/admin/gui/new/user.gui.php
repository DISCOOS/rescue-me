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
    <button class="btn btn-primary" type="submit">Opprett</button>
</form>