<?
    if(isset($_ROUTER['message'])) { 
     insert_error($_ROUTER['message']);
    }
?>
<div class="controls">
    <input type="text" name="name" class="span5" placeholder="Fullt navn" autofocus required>
</div>
<div class="controls controls-row">
    <input class="span3" type="email" name="email" placeholder="E-postadresse" required>
    <input class="span2" type="tel" name="mobile" placeholder="Mobiltelefon" required pattern="[4|9]{1}[0-9]{7}">
</div>
<div class="controls">
    <input type="password" name="password" class="span3" placeholder="Passord" required id="pwd">
    <input type="password" name="confirm-pwd" class="span3" placeholder="Gjenta passord" required equalto="#pwd">
</div>