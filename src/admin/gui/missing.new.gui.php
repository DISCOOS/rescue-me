<?
    use RescueMe\User;    
    use RescueMe\Locale;    
    use RescueMe\Properties;

    $user = User::current();
    
    $id = $user->id;
    
    $locale = Locale::getCurrentLocale();
    
    $sms_text = T_locale(DOMAIN_SMS, $locale, 'ALERT_SMS_TRACE');
    
    $select = "message/list?id=library&select=m_locale&input=sms_text&locale=$locale";
    
?>
<form method="post" class="form well">
    <div class="form-header">    
        <h3 class="no-wrap"><?=START_NEW_TRACE?></h3>
    </div>
    
<? if(isset($_ROUTER['error'])) { ?>
    <div class="alert alert-error">
        <?= $_ROUTER['error'] ?>
    </div>

<? } elseif(modules_exists('RescueMe\SMS\Provider')) { ?>

    <div class="form-body">
        
        <div class="column pull-left">
    
            <fieldset>
                <legend><?=NAME?></legend>

                <input class="input-block-level" type="text" id="m_name" name="m_name" placeholder="<?=T_('Place, region or country')?>" autofocus required>

            </fieldset>

            <fieldset>
                <legend><?=MOBILE_PHONE?></legend>

                <div class="row-fluid">
                    <div class="span4">
                        <label for="m_mobile"><?=COUNTRY_CODE?></label>
                        <select class="input-block-level" id="m_mobile_country" name="m_mobile_country" placeholder="<?=SELECT_COUNTRY?>" required>
                            <?= insert_options(Locale::getCountryNames(), Locale::getCurrentCountryCode(), false); ?>
                        </select>
                    </div>
                    <div class="span8">
                        <label for="m_mobile"><?=PHONE_NUMBER?></label>
                        <input class="input-block-level" type="tel" id="m_mobile" name="m_mobile" placeholder="<?=NUMBERS_ONLY_NO_SPACES?>" required pattern="[0-9]*">
                    </div>
                </div>

            </fieldset>
            
        </div>
        
        <div class="column pull-right">
            
            <fieldset>
                <legend><?=REFERENCE?></legend>

                <input class="input-block-level" type="text" id="op_ref" name="op_ref" placeholder="<?=REFERENCE_EXAMPLES?>">

            </fieldset>
            
            <fieldset>
                <legend><?=REPORT_TO?></legend>

                <div class="row-fluid">
                    <div class="span4">
                        <label for="mb_mobile_country"><?=COUNTRY_CODE?></label>
                        <select class="input-block-level" id="mb_mobile_country" name="mb_mobile_country" placeholder="<?=SELECT_COUNTRY?>" required>
                            <?= insert_options(Locale::getCountryNames(), $user->mobile_country, false); ?>
                        </select>
                    </div>
                    <div class="span8">
                        <label for="m_mobile"><?=PHONE_NUMBER?></label>
                        <input class="input-block-level" type="tel" id="m_mobile" name="mb_mobile" value="<?=$user->mobile?>" placeholder="<?=NUMBERS_ONLY_NO_SPACES?>" required pattern="[0-9]*">
                    </div>
                </div>

            </fieldset>

        </div>
        
        <div class="fill">
            
            <fieldset>
                <legend><?=MESSAGE?></legend>

                <div class="row-fluid">
                    <div class="span2">
                        <label for="m_locale"><?=LANGUAGE?></label>
                        <select class="field input-block-level span12" id="m_locale" name="m_locale" placeholder="<?=SELECT_LANGUAGE?>" required>
                            <?=insert_options(Locale::getLanguageNames(), Locale::getCurrentLocale(), false); ?>
                        </select>
                    </div>
                    <div class="span8">
                        <label for="sms_text"><?=SMS?></label>
                        <textarea class="field span12" id="sms_text" name="sms_text" required rows="1"><?=$sms_text?></textarea>
                     </div>
                    <div class="span2">
                        <label for="sms_text"><?=LIBRARY?></label>
                        <a class="btn span12" data-toggle="modal" data-target="#library" href="<?=ADMIN_URI.$select?>">
                             <b class="icon icon-book"></b><?=SELECT?>...
                        </a>
                     </div>
                </div>
            </fieldset>
        </div>
    <? if(Properties::get(Properties::TRACE_ALERT_NEW, $id) === Properties::YES) { ?>
        <div class="fill">            
            <div class="alert alert-info">
                <button type="button" data-toggle="readmore" class="toggle btn btn-mini btn-info corner-ul">
                <?=MORE?>...</button>

                <?= sprintf(REMEMBER_TO_INCLUDE_LINK,'<span class="label">%LINK%</span>',TITLE)?>

                <div id="readmore" style="display: none;">
                    <br />
                    <h4><?=T_('Standard message')?></h4>
                    <br />
                    <div class="alert"><?=$sms_text?></div>
                    <h4><?=T_('Location script')?></h4>
                    <p><?=T_('When the user clicks on the link a webpage is downloaded which contain a script that attempts to locate the mobile phone. The user must authorize the script access before location can be determined')?><p/>
                    <h5><?=T_('Script download time')?></h5>
                    <p><?=T_('The location script is compressed (gzip, 1.8KB). Is should not take more than a second to download this even on a low-bandwidth network (2G). If it does, the user must be patient and wait until the script is downloaded.')?><p/>
                    <h5><?=T_('Repeated localization')?></h5>
                    <p><?=T_('If the location is inaccurate, the script will continue to listen for location updates until desired accuracy or maximum wait time is is reached. A count-down is shown during this time. Last known location is presented to the user, which allow the user to read out the location over the phone or send ith with an SMS (tell the user to click on the link presented to the user when the script timed out)')?>
                    <p><?=sprintf(T_('Desired accurary (location.desired.accuracy), maximum wait time (location.max.wait) and maximum location age (location.max.age) can be configured on page %1$s'),'<a href="'.ADMIN_URI.'setup#general">' . SETUP .' </a>.')?><p/>
                    <p><?=T_('All traces are listed at')?> <a href="<?=ADMIN_URI?>missing/list">admin/missing/list</a>.<p/>
                </div>
            </div>
        </div>
    <? } ?>   
    </div>
        
    <div class="clearfix"></div>
        
	<div class="form-footer">
         <div class="row-fluid">
            <button type="submit" class="btn btn-success span2 column"><b class="icon icon-envelope icon-white"></b><?=CREATE?></button>
            <select id="m_type" name="m_type" class="span2" >
                <? insert_options(RescueMe\Operation::titles(), 'trace'); ?>
            </select>            
        </div>
    </div>

</form>

<?     
    } 

    // Insert modal message selector
    insert_dialog_selector("library", LIBRARY, LOADING);

?>
