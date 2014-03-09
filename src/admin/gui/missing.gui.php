<?
    use RescueMe\User;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;
    
    $id = input_get_int('id');

    $missing = Missing::get($id);

    if($missing === false)
    {
        insert_alert('Ingen registrert');
    }
    else
    {        
    
        $positions = $missing->getPositions();
        $name = $missing->name;
        if(Operation::isClosed($missing->op_id)) {
            $name .= " ("._("Closed").")";
        }

?>
<div>
    <h3 class="pagetitle"><?= $name ?></h3>
<?
        if(isset($_ROUTER['error'])) { ?>
    
        <div class="alert alert-error">
            <strong>En feil oppsto!</strong><br />
            <?= $_ROUTER['error'] ?>
        </div>
    <?
        }

        $user_id = User::currentId();
        $format = Properties::get(Properties::MAP_DEFAULT_FORMAT, $user_id);
        $top = (Properties::get(Properties::TRACE_BAR_LOCATION, $user_id) === Properties::TOP);
        $collaped = (Properties::get(Properties::TRACE_BAR_STATE, $user_id) === Properties::COLLAPSED);
        $details = explode(',',Properties::get(Properties::TRACE_DETAILS, $user_id));
        if($missing->last_pos->timestamp>-1) {
            $position = format_pos($missing->last_pos, $format);
            $located = format_since($missing->last_pos->timestamp);
        } else {
            $position = format_pos(null, $format);
        }
        if($missing->sms_sent !== null) {
            $sent = format_since($missing->sms_sent);
        } else {
            $sent = _('Ukjent');
        }            
        if($missing->sms_delivery !== null) {
            $delivered = format_since($missing->sms_delivery);
        } else {
            $delivered = _('Ukjent');
        }            
        if($missing->answered !== null) {
            $response = format_since($missing->answered);
        } else {
            $response = _('Ukjent');
        }            

        if($top) {
            insert_trace_bar($missing, $collaped);
        }
            
    ?>
    
    <div class="infos clearfix pull-left">
    <? if(in_array(Properties::TRACE_DETAILS_LOCATION, $details)) { ?>
        <div class="info pull-left">
            <label class="label label-info position" data-pan-to="<?= count($positions) ?>">
                <?=_('Siste posisjon')?></label> <?= $position ?>
        </div>
    <? } if (empty($located) === false && in_array(Properties::TRACE_DETAILS_LOCATION_TIME, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=_('Posisjon mottatt')?></label> 
            <span class="label label-success"><?= $located ?></span>
        </div>
    <? } if (in_array(Properties::TRACE_DETAILS_LOCATION_URL, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=_('Sporingslenke')?></label> 
            <span class="label label-success">
                <?= str_replace("#missing_id", encrypt_id($missing->id), SMS_LINK); ?>
            </span>
        </div>
    <? } ?>
    </div>

    <? require_once(ADMIN_PATH_GUI.'missing.position.list.gui.php'); ?>
    <div id="map" class="map"></div>
    <div id="sidebar">
        <h4><?=_("Posisjoner &le; 1km")?></h4>
        <ul class="unstyled">
                <?
        $i = 0;
        $displayed = false;

        foreach ($positions as $key=>$value) {
            if ($value->acc < 1000) { 
                        $displayed = true;
                        $timestamp = date('Y-m-d H:i:s', strtotime($value->timestamp));
                        ?>
                <li class="position text-left clearfix well well-small" data-pan-to="<?= $i ?>">
                    <span><?=format_pos($value, $format, false)?> &plusmn; <?= $value->acc ?> m</span>
                    <time datetime="<?= $timestamp ?>"><?= format_since($value->timestamp) ?></time>
                </li>
            <?
            }
            $i++;
        } 
            if (!$displayed) {
                echo '<li class="position clearfix well well-small">'._('Ingen').'</li>';
            }
        ?>
        </ul>
        <h4><?=_("Posisjoner &ge; 1km")?></h4>
        <ul class="unstyled">
        <?
        $i = 0;
            $displayed = false;
        foreach ($positions as $key=>$value) {
            if ($value->acc >= 1000) { 
                $displayed = true;
                $timestamp = date('Y-m-d H:i:s', strtotime($value->timestamp)); 
        ?>
                <li class="position text-left clearfix well well-small" data-pan-to="<?= $i ?>">
                    <span><?=format_pos($value, $format, false)?> &plusmn; <?= $value->acc ?> m</span>
                    <time datetime="<?= $timestamp ?>"><?= format_since($value->timestamp) ?></time>
                </li>
        <?
            }
            $i++;
        }
        
        if ($displayed === false) {
            echo '<li class="position clearfix well well-small">'._('Ingen').'</li>';
        }
        ?>
                
        </ul>
    </div>
    
    <div class="infos clearfix pull-left ">    
                
    <?
        if($top === false) {
            insert_trace_bar($missing, $collaped);
        }
    ?>

    </div>
</div>

    <? } ?>    
