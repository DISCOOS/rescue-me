<?
    use RescueMe\User;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;
    
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
        if(Operation::isClosed($missing->op_id)) {
            $name .= " (".CLOSED.")";
        }

?>
<div>
    <h3 class="pagetitle"><?= $name ?></h3>
<?
        if(isset($_ROUTER['error'])) { ?>
    
        <div class="alert alert-error">
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
            $pan_to = 'data-pan-to="'. (count($positions)-1) . '"';
            $position = format_pos($missing->last_pos, $format, true, $pan_to);
            $located = format_since($missing->last_pos->timestamp);
            $located_state = "success";
        } else {
            $pan_to = '';
            $position = format_pos(null, $format);
            $located = UNKNOWN;
            $located_state = "warning";
        }

    ?>
    
    <? if($top) { insert_trace_bar($missing, $collaped); } ?>
        
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
        <h4><?=sprintf(LOCATIONS_LESS_EQUAL,'1 km')?></h4>
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
                echo '<li class="position clearfix well well-small">'.NONE.'</li>';
            }
        ?>
        </ul>
        <h4><?=sprintf(LOCATIONS_GREATER_THAN,'1 km')?></h4>
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
            echo '<li class="position clearfix well well-small">'.NONE.'</li>';
        }
        ?>
                
        </ul>
    </div>

    <div class="clearfix"></div>
    
    <? if($top === false) { insert_trace_bar($missing, $collaped); } ?>
    
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
