<?
    use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Module;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;
    use RescueMe\SMS\Provider;
    
    $id = input_get_int('id');

    $missing = Missing::get($id);

    if($missing === false)
    {
        insert_alert(NONE_FOUND);
    }
    else
    {        
    
        $positions = $missing->getPositions();
        $name = $missing->name;
        $title = $name;
        if(Operation::isClosed($missing->op_id)) {
            $title .= " [".CLOSED."]";
        } else {
            $mobile = $missing->mobile;
            $mobile_country = $missing->mobile_country;
            $code = Locale::getDialCode($mobile_country);
            $module = Module::get(Provider::TYPE, User::currentId());
            $sms = $module->newInstance();
            $check = ($sms instanceof RescueMe\SMS\Check);
            if($check) {
                $code = $sms->accept($code);
            }
            $title .= ' [<a href="tel:' . "$code$mobile" . '">'."$code$mobile</a>]";
        }

?>
<div>
    <h3 class="pagetitle"><?= $title ?></h3>
<?
        if(isset($_ROUTER['error'])) { ?>
    
        <div class="alert alert-error">
            <strong>En feil oppsto!</strong><br />
            <?= $_ROUTER['error'] ?>
        </div>
    
    <?
        }

        $user_id = User::currentId();
        $params = Properties::getAll($user_id);
        $top = ($params[Properties::TRACE_BAR_LOCATION] === Properties::TOP);
        $collapsed = ($params[Properties::TRACE_BAR_STATE] === Properties::COLLAPSED);
        $details = explode(',', $params[Properties::TRACE_DETAILS]);
        if($missing->last_pos->timestamp>-1) {
            $pan_to = 'data-pan-to="'. (count($positions)-1) . '"';
            $position = format_pos($missing->last_pos, $params, $pan_to);
            $located = format_since($missing->last_pos->timestamp);
            $located_state = "success";
        } else {
            $pan_to = '';
            $position = format_pos(null, $params);
            $located = UNKNOWN;
            $located_state = "warning";
        }

    ?>
    
    <? if($top) { insert_trace_bar($missing, $collapsed); } ?>
        
    <div class="infos pull-left">
    <? if(in_array(Properties::TRACE_DETAILS_LOCATION, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info label-position" <?=$pan_to?>>
                <?=LAST_LOCATION?></label> <?= $position ?>
        </div>
    <? } if (in_array(Properties::TRACE_DETAILS_LOCATION_TIME, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=LOCATION_RECEIVED?></label> 
            <span class="label label-<?=$located_state?>"><?= $located ?></span>
        </div>
    <? } ?>
    </div>
    
    <? require_once(ADMIN_PATH_GUI.'missing.position.list.gui.php'); ?>
    <div id="map" class="map"></div>
    <div id="sidebar">
        <h4 id="under1kmtitle" class="hide"><?=sprintf(LOCATIONS_LESS_EQUAL,'1 km')?></h4>
        <ul class="unstyled" id="under1km"></ul>
        </ul>
        <h4 id="over1kmtitle" class="hide"><?=sprintf(LOCATIONS_GREATER_THAN,'1 km')?></h4>
        <ul class="unstyled" id="over1km"></ul>
    </div>

    <div class="clearfix"></div>
    
    <? if($top === false) { insert_trace_bar($missing, $collapsed); } ?>
    
    <div class="infos clearfix pull-left">
        
    <? if (in_array(Properties::TRACE_DETAILS_REFERENCE, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=REFERENCE?></label> 
            <span class="label label-<?=empty($missing->op_ref) ? 'warning' : 'success' ?>">
                <?= empty($missing->op_ref) ? UNKNOWN : $missing->op_ref ?>
            </span>
        </div>
    <? } if (in_array(Properties::TRACE_DETAILS_LOCATION_URL, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=LOCATION_LINK?></label> 
            <span class="label label-success">
                <?= str_replace("#missing_id", encrypt_id($missing->id), LOCATE_URL); ?>
            </span>
        </div>
    <? } ?>
    </div>    
                
    
</div>

    <? } ?>    
