<?
    use RescueMe\Domain\User;
    use RescueMe\Domain\Missing;
    use RescueMe\Domain\Operation;
    use RescueMe\Properties;
    
    $id = input_get_int('id');

    $missing = Missing::get($id);

    if($missing === false)
    {
        insert_alert(T_('None found'));
    }
    else
    {        
    
        $positions = $missing->getPositions();
        $name = $missing->name;
        if(Operation::isClosed($missing->op_id)) {
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
        if($missing->last_pos->timestamp>-1) {
            $pan_to = 'data-pan-to="'. (count($positions)-1) . '"';
            $position = format_pos($missing->last_pos, $params, $pan_to);
            $located = format_since($missing->last_pos->timestamp);
            $located_state = "success";
        } else {
            $pan_to = '';
            $position = format_pos(null, $params);
            $located = T_('Unknown');
            $located_state = "warning";
        }

    ?>
    
    <? if($top) { insert_trace_bar($missing, $collapsed); } ?>
        
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
    
    <? require_once(ADMIN_PATH_GUI.'missing.position.list.gui.php'); ?>
    <div id="map" class="map"></div>
    <div id="sidebar">
        <h4 id="lt1kmtitle" class="hide"><?=sprintf(T_('Locations &le; %1$s'),'1 km')?></h4>
        <ul class="unstyled" id="lt1km"></ul>
        </ul>
        <h4 id="ge1kmtitle" class="hide"><?=sprintf(T_('Locations &gt; %1$s'),'1 km')?></h4>
        <ul class="unstyled" id="ge1km"></ul>
    </div>

    <div class="clearfix"></div>
    
    <? if($top === false) { insert_trace_bar($missing, $collapsed); } ?>
    
    <div class="infos clearfix pull-left">
        
    <? if (in_array(Properties::TRACE_DETAILS_REFERENCE, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Reference')?></label>
            <span class="label label-<?=empty($missing->op_ref) ? 'warning' : 'success' ?>">
                <?= empty($missing->op_ref) ? T_('Unknown') : $missing->op_ref ?>
            </span>
        </div>
    <? } if (in_array(Properties::TRACE_DETAILS_LOCATION_URL, $details)) { ?>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Location link')?></label>
            <span class="label label-success">
                <?= str_replace("#missing_id", encrypt_id($missing->id), LOCATE_URL); ?>
            </span>
        </div>
    <? } ?>
    </div>    
                
    
</div>

    <? } ?>    
