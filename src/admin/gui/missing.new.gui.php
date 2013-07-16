<h3>Start sporing av savnet</h3>
<?php if(isset($_ROUTER['message'])) { ?>
	<div class="alert alert-error">
		<strong>En feil oppsto!</strong><br />
		<?= $_ROUTER['message'] ?>
	</div>

<? } elseif(modules_exists("\RescueMe\SMS\Provider")) { ?>

<form method="post">
	<fieldset class="span6 new-missing pull-left">
		<legend>Den savnede</legend>

		<label>Savnedes navn</label>
		<input class="input-block-level" type="text" name="m_name" placeholder="Fullt navn" autofocus required>
		<span class="help-block"></span>

		<label>Savnedes mobilnummer</label>
		<input class="input-block-level" type="tel" name="m_mobile" placeholder="Kun siffer, ingen mellomrom" required pattern="[4|9]{1}[0-9]{7}">
		<span class="help-block"></span>
	</fieldset>
	
	<fieldset class="span6 new-missing pull-left">
		<legend>Om deg</legend>

		<label>Ditt navn</label>
		<input class="input-block-level"  type="text" name="mb_name" placeholder="Fullt navn" required>
		<span class="help-block"></span>

		<label>Din e-postadresse</label>
		<input class="input-block-level" type="email" name="mb_mail" placeholder="E-postadresse" required>
		<span class="help-block"></span>

		<label>Ditt mobilnummer</label>
		<input class="input-block-level" type="tel" name="mb_mobile" placeholder="Kun siffer, ingen mellomrom"required pattern="[4|9]{1}[0-9]{7}">
		<span class="help-block"></span>
	</fieldset>

	<div class="clearfix"></div>
    <div class="alert alert-info">
        <em>En SMS sendes automatisk til den savnede når sporing opprettes</em>
        <button type="button" data-toggle="readmore" class="toggle btn btn-mini btn-info pull-right">Les mer</button>	
        <div id="readmore" style="display:none;">
            <h4>SMS-tekst</h4>
            <div class="alert"><?= SMS_TEXT ?></div>
            <h4>Sporingsside</h4>
            <p>Når brukeren trykker på lenken åpnes en nettside som posisjonerer brukeren, 
            og h*n må deretter godkjenne deling av posisjon i nettleseren.</p>
            <p>
                <strong>Lastetid</strong>
                <br />
                Nettsiden er 1,4Kb, noe som burde ta litt mindre enn ett sekund på dårlig mobilnett. 
                Det er likevel viktig at brukeren er tålmodig, og venter lengre enn dette hvis siden ikke åpnes.
            </p>
            <p>
                <strong>Gjentatt posisjonering</strong>
                <br />
                Hvis posisjonen er unøyaktig, vil nettsiden lastes på nytt igjen etter 10 sekunder.
                Brukeren vil da se nedtellingen, og siden åpnes på nytt. Dette vil gjentas inntil 10 ganger.
            <p>
                Alle sporinger er tilgjengelig på admin-siden.
            <p/>
            <p>
                <strong>OBS:</strong> Lastetiden vil mest sannsynlig være rimelig lik første åpning av sporingssiden.
            </p>

        </div>
    </div>        

	<div class="clearfix"></div>
    <button type="submit" class="btn btn-success">Opprett sporing</button>
	
</form>

<? } ?>