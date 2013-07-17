<form class="form-user" method="post">
    <h3 ><?=NEW_USER?></h3>
    <?php
        if(isset($_ROUTER['message'])) { 
    ?>
        <div class="alert alert-error"><?= $_ROUTER['message'] ?></div>
    <?
        } 
    ?>    
    <input type="text" name="name" class="input-block-level" placeholder="Fullt navn" autofocus required>
    <input type="email" name="email" class="input-block-level" placeholder="E-postadresse" required>
    <input type="password" name="password" class="input-block-level" placeholder="Passord" required id="pwd">
    <input type="password" name="confirm-pwd" class="input-block-level" placeholder="Gjenta passord" required equalto="#pwd">
    <input type="tel" name="mobile" class="input-block-level" placeholder="Mobiltelefon" required pattern="[4|9]{1}[0-9]{7}">
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><?= CREATE ?></button>
        <a type="button" class="btn" href="javascript: history.go(-1)"><?= CANCEL ?></a>
    </div>    
</form>
