<form class="form-user" method="post">
    <h3 ><?=EDIT_USER?></h3>
    <?php
        if(isset($_ROUTER['message'])) { 
    ?>
        <div class="alert alert-error"><?= $_ROUTER['message'] ?></div>
    <?
        } 
    ?>    
    <input type="text" name="name" class="input-block-level" placeholder="Fullt navn" value="<?= $user->name ?>" autofocus required >
    <input type="email" name="email" class="input-block-level" placeholder="E-postadresse" value="<?= $user->email ?>" required>
    <input type="tel" name="mobile" class="input-block-level" placeholder="Mobiltelefon" value="<?= $user->mobile ?>" required pattern="[4|9]{1}[0-9]{7}">
    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><?= CREATE ?></button>
        <a type="button" class="btn" href="javascript: history.go(-1)"><?= CANCEL ?></a>
    </div>    
</form>
