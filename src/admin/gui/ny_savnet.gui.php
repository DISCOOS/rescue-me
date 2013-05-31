<h3>Start sporing av savnet</h3>
<?php
if(isset($_SAVN['message'])) { ?>
	<div class="alert alert-error">
		<strong>En feil oppsto!</strong><br />
		<?= $_SAVN['message'] ?>
	</div>
<?
} ?>
<form method="post">
	<fieldset class="span6 ny_savnet pull-left">
		<legend>Informasjon om den savnede</legend>

		<label>Savnedes navn</label>
		<input type="text" name="m_name" placeholder="Fullt navn" autofocus required>
		<span class="help-block"></span>

		<label>Savnedes mobilnummer</label>
		<input type="tel" name="m_mobile" placeholder="Kun siffer, ingen mellomrom"required pattern="[4|9]{1}[0-9]{7}">
		<span class="help-block"></span>
	</fieldset>
	
	<fieldset class="span6 ny_savnet pull-left">
		<legend>Informasjon om deg</legend>

		<label>Ditt navn</label>
		<input type="text" name="mb_name" placeholder="Fullt navn" required>
		<span class="help-block"></span>

		<label>Din e-postadresse</label>
		<input type="email" name="mb_mail" placeholder="" required>
		<span class="help-block"></span>

		<label>Ditt mobilnummer</label>
		<input type="tel" name="mb_mobile" placeholder="Kun siffer, ingen mellomrom"required pattern="[4|9]{1}[0-9]{7}">
		<span class="help-block"></span>
	</fieldset>

	<div class="clearfix"></div>
	<div class="span6 alert alert-info">
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
				Nettsiden er 1,9Kb, noe som burde ta litt mindre enn ett sekund på dårlig mobilnett. 
				Det er likevel viktig at brukeren er tålmodig, og venter lengre enn dette hvis siden ikke åpnes.
			</p>
			<p>
				<strong>Gjentatt posisjonering</strong>
				<br />
				Hvis posisjonen er unøyaktig, vil nettsiden lastes på nytt igjen etter 10 sekunder.
				Brukeren vil da se nedtellingen, og siden åpnes på nytt. Dette vil gjentas inntil 10 ganger.
				<br />
				Alle sporinger er tilgjengelig på admin-siden.
				<br />
				<strong>OBS:</strong> Lastetiden vil mest sannsynlig være rimelig lik første åpning av sporingssiden.
			</p>
			
		</div>
	</div>

	<div class="clearfix"></div>
	<button type="submit" class="btn btn-success">Opprett sporing</button>
</form>