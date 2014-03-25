<?php
    
    use RescueMe\Locale;
    use RescueMe\Template;
    use RescueMe\SMS\Message;

    ob_start();
    
    $id = input_get_string('id');
    $select = input_get_string('select');
    $input = input_get_string('input');
    $locale = input_get_string('locale');
    
    $onselect = "$('#$select').val($('#message-locale').val()); $('#$input').val('%1\$s');";
    $load_url = ADMIN_URI."message/list?id=$id&select=m_locale&input=sms_text&locale=";
    
    $messages[] = Message::get($locale, 'ALERT_SMS_TRACE');
    $templates = Template::getAll(Template::MESSAGE);
    if($templates !== false) {
        $messages = array_merge($messages, $templates);
    }
?>

<div class="row-fluid">
    <div class="span8">
        <label for="message-locale"><?=LANGUAGE?></label>
        <select id="message-locale" 
                name="message-locale" 
                class="field input-block-level span12" 
                placeholder="<?=SELECT_LANGUAGE?>" 
                required
                onchange="R.modal.load('<?=$load_url?>'+$('#message-locale').val(),'#<?=$id?>')">
            <?=insert_options(Locale::getLanguageNames(), $locale, false); ?>
        </select>
    </div>
    <div class="span4">
        <label for="search"><?=MESSAGES?></label>
        <input id="search" type="text" 
               class="span12 search-query" 
               placeholder="<?=SEARCH?>"
               data-target="messages">
    </div>
 </div>

<? list($domain) = set_system_locale(DOMAIN_SMS, $locale); ?>

<div class="row-fluid" style="max-height: 300px; overflow-x: hidden; overflow-y: auto;">
    <table id="messages" class="table table-striped">
        <tbody class="searchable">
        <? foreach($messages as $message) { ?>
            <tr><td><?=$message?></td><td>
                    <a class="btn btn-primary pull-right" 
                       data-dismiss="modal" 
                       aria-hidden="true"
                       onclick="<?=sprintf($onselect, $message)?>"><?=SELECT?></a></td></tr>
        <? } ?>
        </tbody>
    </table>
</div>

<?
    set_system_locale($domain, $locale); 
    
    return create_ajax_response(ob_get_clean());
    
?>
