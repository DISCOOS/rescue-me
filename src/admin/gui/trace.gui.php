<?
    use RescueMe\User;
    use RescueMe\Mobile;
    use RescueMe\Trace;
    use RescueMe\Properties;
    
    $id = input_get_int('id');

    $mobile = Mobile::get($id);

    if($mobile === false)
    {
        insert_alert(T_('None found'));
    }
    else
    {        
    
        $positions = $mobile->getPositions();
        $name = $mobile->name;
        if(Trace::isClosed($mobile->trace_id)) {
            $name .= ' ('.T_('Closed').')';
        }

?>
<div>
    <h3 class="pagetitle"><?= $name ?></h3>
<?
        if(isset($_ROUTER['error'])) {
            insert_error($_ROUTER['error']);
        }

        $user_id = User::currentId();
        $params = Properties::getAll($user_id);
        $top = ($params[Properties::TRACE_BAR_LOCATION] === Properties::TOP);
        $collapsed = ($params[Properties::TRACE_BAR_STATE] === Properties::COLLAPSED);
        $details = explode(',', $params[Properties::TRACE_DETAILS]);
        if($mobile->last_pos->timestamp>-1) {
            $pan_to = 'data-pan-to="'. (count($positions)-1) . '"';
            $position = format_pos($mobile->last_pos, $params, $pan_to);
            $located = format_since($mobile->last_pos->timestamp);
            $located_state = "success";
        } else {
            $pan_to = '';
            $position = format_pos(null, $params);
            $located = T_('Unknown');
            $located_state = "warning";
        }

    ?>
    
    <? if($top) { insert_trace_bar($mobile, $collapsed); } ?>
        
    <div class="infos pull-left">
    <? if(in_array(Properties::TRACE_DETAILS_LOCATION, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info label-position" <?=$pan_to?>>
                <?=T_('Last location')?></label> <?= $position ?>
        </div>
    <? } if (in_array(Properties::TRACE_DETAILS_LOCATION_TIME, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Location received')?></label>
            <span class="label label-<?=$located_state?>"><?= $located ?></span>
        </div>
    <? } ?>
    </div>
    
    <? require_once(ADMIN_PATH_GUI . 'trace.position.list.gui.php'); ?>
    <div id="map" class="map"></div>
    <div id="sidebar">
        <h4 id="under1kmtitle" class="hide"><?=sprintf(T_('Locations &le; %1$s'),'1 km')?></h4>
        <ul class="unstyled" id="under1km"></ul>
        </ul>
        <h4 id="over1kmtitle" class="hide"><?=sprintf(T_('Locations &gt; %1$s'),'1 km')?></h4>
        <ul class="unstyled" id="over1km"></ul>
    </div>

    <div class="clearfix"></div>
    
    <? if($top === false) { insert_trace_bar($mobile, $collapsed); } ?>
    
    <div class="infos clearfix pull-left">
        
    <? if (in_array(Properties::TRACE_DETAILS_REFERENCE, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Reference')?></label>
            <span class="label label-<?=empty($mobile->trace_ref) ? 'warning' : 'success' ?>">
                <?= empty($mobile->trace_ref) ? T_('Unknown') : $mobile->trace_ref ?>
            </span>
        </div>
    <? } if (in_array(Properties::TRACE_DETAILS_LOCATION_URL, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Location link')?></label>
            <span class="label label-success">
                <?= str_replace("#mobile_id", encrypt_id($mobile->id), LOCATE_URL); ?>
            </span>
        </div>
    <? } ?>
    </div>    
                
    
</div>

    <? } ?>    
