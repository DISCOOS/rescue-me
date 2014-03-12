<?
    use RescueMe\Locale;    
    
?>
<form method="post" class="form well">
    <div class="form-header">    
        <h3 class="no-wrap"><?=_("Start sporing")?></h3>
    </div>
    
    <?php if(isset($_ROUTER['error'])) { ?>
        <div class="alert alert-error">
            <strong>En feil oppsto!</strong><br />
            <?= $_ROUTER['error'] ?>
        </div>

    <? } elseif(modules_exists("RescueMe\SMS\Provider")) { ?>

    <div class="form-body">
        
        <div class="new-missing pull-left">
    
            <fieldset>
                <legend><?=_('Navn')?></legend>

                <input class="input-block-level" type="text" id="m_name" name="m_name" placeholder="Sted, landsdel, eller savnedes navn" autofocus required>

            </fieldset>

            <fieldset>
                <legend><?=_('Mobiltelefon')?></legend>

                <div class="row-fluid">
                    <div class="span4">
                        <label for="m_mobile">Land-kode</label>
                        <select class="input-block-level" id="m_mobile_country" name="m_mobile_country" placeholder="Velg land" required>
                            <?= insert_options(Locale::getCountryNames(), Locale::getCurrentCountryCode()); ?>
                        </select>
                    </div>
                    <div class="span8">
                        <label for="m_mobile">Savnedes mobilnummer</label>
                        <input class="input-block-level" type="tel" id="m_mobile" name="m_mobile" placeholder="Kun siffer, ingen mellomrom" required pattern="[0-9]*">
                    </div>
                </div>

            </fieldset>
            
        </div>
        
        <div class="new-missing pull-right">
            
            <fieldset>
                <legend><?=_('Aksjonsreferanse')?></legend>

                <input class="input-block-level" type="text" id="op_ref" name="op_ref" placeholder="SAR- eller AMIS-nr" required>

            </fieldset>
            
            <fieldset>
                <legend><?=_('Rapporter til')?></legend>

                <div class="row-fluid">
                    <div class="span4">
                        <label for="mb_mobile_country">Land-kode</label>
                        <select class="input-block-level" id="mb_mobile_country" name="mb_mobile_country" placeholder="Velg land" required>
                            <?= insert_options(Locale::getCountryNames(), $user->mobile_country); ?>
                        </select>
                    </div>
                    <div class="span8">
                        <label for="m_mobile"><?=_('Mobilnummer')?></label>
                        <input class="input-block-level" type="tel" id="m_mobile" name="mb_mobile" value="<?=$user->mobile?>" placeholder="Kun siffer, ingen mellomrom" required pattern="[0-9]*">
                    </div>
                </div>

            </fieldset>

        </div>
        
        <div class="new-missing pull-left">
            
            <fieldset>
                <legend><?=_('Melding')?></legend>

                <div class="row-fluid">
                    <textarea class="field span12" id="sms_text" name="sms_text" required><?=SMS_TEXT?></textarea>
                </div>

                <div class="alert alert-info" style="position: relative;">
                    <div> 
                        <span style="color: red;">Husk å skrive inn <span class="label">%LINK%</span> 
                        slik at RescueMe kan sette inn med riktig lenke til sporingssiden</span>.
                    </div>
                    <button type="button" data-toggle="readmore" class="toggle btn btn-mini btn-info"
                            style="position: absolute; right: 0; bottom: 0;">Mer...</button>
                            
                    <div id="readmore" style="display:none;">
                        <br />
                        <h4>Standard melding</h4>
                        
                        <div class="alert"><?= SMS_TEXT ?></div>
                        <h4>Sporingsside</h4>
                        <p>Når brukeren trykker på lenken åpnes en nettside som vil forsøke å posisjonerer 
                            mobiltelefonen. Brukeren må godkjenne deling av posisjon i nettleseren før posisjonen 
                            kan bestemmes.
                        </p><p>
                            <strong>Lastetid</strong>
                            <br />
                            Nettsiden er komprimert (1.8KB). Det burde ta mindre enn ett sekund på dårlig 
                            mobilnett (2G) å laste den ned. Det er likevel viktig at brukeren er tålmodig, og venter 
                            lengre enn dette hvis siden ikke åpnes.
                        </p><p>
                            <strong>Gjentatt posisjonering</strong>
                            <br />
                            Hvis posisjonen er unøyaktig, vil nettsiden vente til posisjon med ønsket nøyaktighet 
                            er funnet, eller maksimum ventetid er nådd. En nedtelling vises mens dette foregår. 
                            Siste posisjon vises også til brukeren, slik at denne kan leses opp på telefonen, eller 
                            sendes på SMS (be brukeren klikke på linken bak posisjonen).
                        <p/><p>
                            Ønsket nøyaktighet (location.desired.accuracy), maksimum ventetid 
                            (location.max.wait) og maximum alder på gammel posisjon (location.max.age) 
                            kan konfigureres på siden<a href="<?=ADMIN_URI?>setup#general">Oppsett</a>.
                        <p/><p>
                            Alle sporinger er tilgjengelig på <a href="<?=ADMIN_URI?>/missing/list">admin/missing/list</a>.
                        <p/>

                    </div>
                </div>

            </fieldset>
        </div>
    </div>
        
    <div class="clearfix"></div>
        
	<div class="form-footer">
         <div class="row-fluid">
            <button type="submit" class="btn btn-success span2 new-missing"><?=_('Opprett')?></button>
            <select id="m_type" name="m_type" class="span2" >
                <? insert_options(RescueMe\Operation::titles(), 'trace'); ?>
            </select>            
        </div>
    </div>

</form>

<? } ?>
