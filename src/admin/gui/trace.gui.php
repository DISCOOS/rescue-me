<?
    use RescueMe\Device\Lookup;
    use RescueMe\Locale;
    use RescueMe\Manager;
    use RescueMe\User;
    use RescueMe\Mobile;
    use RescueMe\Trace;
    use RescueMe\Properties;
    
    $id = input_get_int('id');

    $mobile = Mobile::get($id);

    $user_id = User::currentId();

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

        $device = null;
        $supported = null;
        if(($requests = $mobile->getRequests()) !== false) {
            $request = current($requests);
            $factory = Manager::get(Lookup::TYPE, $user_id);
            /** @var Lookup $lookup */
            $lookup = $factory->newInstance();
            $device = $lookup->device($request);

            $supported = isset($device->device_supports_xhr2)
                && isset($device->device_supports_geolocation)
                && $device->device_supports_xhr2 === 'True'
                && $device->device_supports_geolocation=== 'True';
        }

        // Get mobile network from mcc-mnc network code
        $network = get_mobile_network($mobile->network_code);

?>
<div>
    <h3 class="pagetitle"><?= $name ?></h3>
<?
        if(isset($_ROUTER['error'])) {
            insert_error($_ROUTER['error']);
        }

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

    <div class="clearfix"></div>

    <div class="infos clearfix pull-left">
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Device type')?></label>
            <span class="label label-<?= isset($device->device_type) ? 'success' : 'warning' ?>">
                <?= isset($device->device_type) ? $device->device_type : T_('Unknown')?>
            </span>
        </div>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Is SmartPhone')?></label>
            <span class="label label-<?= isset($device->device_is_smartphone) ? 'success' : 'warning' ?>">
                <?= isset($device->device_is_smartphone) ? ($device->device_is_smartphone === 'True' ? T_('Yes') : T_('No'))
                    : T_('Unknown') ?>
            </span>
        </div>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Supports location')?></label>
            <span class="label label-<?= $supported  ? 'success' : 'warning' ?>">
                <?= $supported  ? T_('Yes') : T_('No')?>
            </span>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="infos clearfix pull-left">
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Is Phone')?></label>
            <span class="label label-<?= isset($device->device_is_phone) ? 'success' : 'warning' ?>">
                <?= isset($device->device_is_phone) ? ($device->device_is_phone === 'True' ? T_('Yes') : T_('No'))
                    : T_('Unknown')?>
            </span>
        </div>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Device OS')?></label>
            <span class="label label-<?= isset($device->device_os_name) ? 'success' : 'warning' ?>">
                <?= isset($device->device_os_name) ? $device->device_os_name
                    . " ({$device->device_os_version})": T_('Unknown')?>
            </span>
        </div>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Device Browser')?></label>
            <span class="label label-<?= isset($device->device_browser_name) ? 'success' : 'warning' ?>">
                <?= isset($device->device_browser_name) ? $device->device_browser_name
                    . " ({$device->device_browser_version})" : T_('Unknown') ?>
            </span>
        </div>
    </div>

    <div class="infos clearfix pull-left">
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Phone Number')?></label>
            <span class="label label-success">
                <?= Locale::getDialCode($mobile->country).$mobile->number ?>
            </span>
        </div>
        <div class="info pull-left no-wrap">
            <label class="label label-info"><?=T_('Phone Network')?></label>
            <span class="label label-<?= $network ? 'success' : 'warning' ?>">
                <?= $network ? (sprintf('%s (%s)', $network['network'], $network['country']))  : T_('Unknown') ?>
            </span>
        </div>
    </div>


</div>

    <? } ?>    