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
        $requestCount = 0;
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

            $requestCount = count($requests);

        }

        // Get mobile network from mcc-mnc network code
        $network = get_mobile_network($mobile->network_code);

        // Get undelivered messages
        $messages = $mobile->getUndeliveredMessages();
        $messageCount = ($messages === false ? 0 : count($messages));

        // Get reported errors
        $errors = $mobile->getErrors();
        $errorCount = ($errors === false ? 0 : count($errors));


    ?>

    <div style="width: 100%; height: 50px; border: 0; border-bottom:1px solid lightgray; align-items: stretch">
        <div class="pull-left no-wrap">
            <h3 class="pagetitle"><?= $name ?></h3>
        </div>
	<div class="hidden-phone" style="width:100%">
        <? if(!Trace::isClosed($mobile->trace_id)) { ?>
        <div class="pull-right no-wrap">
            <span class="pagetitle">
                <a class="btn btn-small" href="/admin/trace/edit/<?= $id ?>" data-title="Start ny sporing">
                    <b class="icon icon-edit"></b><?= T_('Edit') ?>
                </a>
            </span>
        </div>
        <div class="pull-right no-wrap" style="margin-right: 5px;">
            <span class="pagetitle">
                <a class="btn btn-small" data-toggle="modal" data-target="#confirm"
                   data-content="<?= sprintf(T_('Do you want to resend SMS to %1$s?'), "<u>{$mobile->number}</u>") ?>"
                   data-onclick="R.ajax('<?= ADMIN_URI . "trace/resend/{$mobile->id}" ?>','#sent-<?= $mobile->id ?>');">
                    <b class="icon icon-envelope"></b><?= T_('Resend') ?>
                </a>
            </span>
        </div>
        <div class="pull-right no-wrap" style="margin-right: 5px;">
            <span class="pagetitle">
                <a class="btn btn-small" data-toggle="modal" href="/admin/trace/close/<?= $id ?>">
                    <b class="icon icon-off"></b><?= T_('Close') ?>
                </a>
            </span>
        </div>
        <? } else { ?>
        <div class="pull-right no-wrap" style="margin-right: 5px;">
            <span class="pagetitle">
                <a class="btn btn-small" href="<?=ADMIN_URI."trace/reopen/{$mobile->id}"?>">
                    <b class="icon icon-edit"></b><?= T_('Reopen') ?>
                </a>
            </span>
        </div>
        <? } ?>
        </div>
    </div>

    <?

        if(isset($_ROUTER['error'])) {
            insert_error($_ROUTER['error']);
        }

        $params = Properties::getAll($user_id);
        $top = ($params[Properties::TRACE_BAR_LOCATION] === Properties::TOP);
        $collapsed = ($params[Properties::TRACE_BAR_STATE] === Properties::COLLAPSED);
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


    <? if($top) { insert_trace_progress($mobile, $collapsed); } ?>

    <div class="clearfix"></div>

    <? if($mobile->errors && $mobile->last_pos->timestamp === -1) { ?>
        <div class="alert alert-error">
        <?
            $header = T_('Trace script has reported errors');
            $items = array();
            foreach($mobile->errors as $number => $count) {
                switch($number) {
                    case 1:
                        $items[] = implode(' ', array(
                            sprintf(T_('Permission denied %s times.'), $count),
                            T_('Ask if location sharing prompt was accepted.')
                        ));
                        break;
                    case 2:
                        $items[] = implode(' ', array(
                            sprintf(T_('Location unavailable %s times.'), $count),
                            T_('Ask if location services are turned on.')
                        ));
                        break;
                    case 3:
                        $items[] = implode(' ', array(
                            sprintf(T_('Location timeout %s times.'), $count),
                            T_('Ask if location services are turned on.')
                        ));
                        break;
                }
            }
            echo sprintf("<b>%s</b> <nl><li>%s</li></nl>",$header, implode('</li><li>',$items));
        ?>
        </div>
    <? } ?>

<!--        --><?//
//            $mobile->last_pos->lat = 59.911491;
//            $mobile->last_pos->lon = 10.757933;
//            $now = new DateTime();
//            $mobile->last_pos->timestamp = $now->getTimestamp();
//        ?>


    <ul class="thumbnails" style="margin-top: 10px;">
        <li class="span6">
            <div class="thumbnail">
                <div class="caption">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th><h4><?=T_('Status')?></h4></th>
                            <t></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Last location')?></td>
                            <td <?=$pan_to?>><?= $position ?></td>
                        </tr>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Location received')?></td>
                            <td><span class="label label-<?=$located_state?>"><?= $located ?></span></td>
                        </tr>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Trace reloads')?></td>
                            <td><span class="label label-<?=
                                $requestCount === 1
                                    ? 'success'
                                    : $requestCount < 10
                                        ? 'warning'
                                        : 'important' ?>">
                                <?=$requestCount?>
                            </span></td>
                        </tr>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Trace errors')?></td>
                            <td><span class="label label-<?=$errorCount === 0 ? 'success' : 'important' ?>">
                                <?=$errorCount?>
                            </span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </li>
        <li class="span6">
            <div class="thumbnail">
                <div class="caption">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th><h4><?=T_('Mobile')?></h4></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Phone Number')?></td>
                            <td><span class="label label-success">
                                <?= $mobile->number
                                    ? Locale::getDialCode($mobile->country).$mobile->number
                                    : T_('Redacted')
                                ?>
                            </span></td>
                        </tr>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Phone Network')?></td>
                            <td><span class="label label-<?= $network ? 'success' : 'warning' ?>">
                                <?= $network ? (sprintf('%s (%s)', $network['network'], $network['country']))  : T_('Unknown') ?>
                            </span></td>
                        </tr>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Undelivered messages')?></td>
                            <td><span class="label label-<?= $messageCount > 0  ? 'warning' : 'success' ?>">
                                <?= $messages === null ? T_('Unknown') : $messageCount?>
                            </span></td>
                        </tr>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Location link')?></td>
                            <td><span class="label label-success">
                                <?= str_replace("#mobile_id", encrypt_id($mobile->id), LOCATE_URL); ?>
                            </span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </li>
    </ul>

    <? require_once(ADMIN_PATH_GUI . 'trace.position.list.gui.php'); ?>
    <div id="map" class="map"></div>

    <div id="sidebar">
        <? insert_last_position_table($mobile); ?>
        <h4 id="under1kmtitle" class="hide" style="border: 0; border-bottom:1px solid lightgray;"><?=sprintf(T_('Locations &le; %1$s'),'1 km')?></h4>
        <ul class="unstyled" id="under1km"></ul>
        </ul>
        <h4 id="over1kmtitle" class="hide" style="border: 0; border-bottom:1px solid lightgray;"><?=sprintf(T_('Locations &gt; %1$s'),'1 km')?></h4>
        <ul class="unstyled" id="over1km"></ul>
    </div>

    <div class="clearfix"></div>
    
    <? if($top === false) { insert_trace_progress($mobile, $collapsed); } ?>

    <ul class="thumbnails" style="margin-top: 20px;">
        <li class="span6">
            <div class="thumbnail">
                <div class="caption">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th><h4><?=T_('Device details')?></h4></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Type')?></td>
                            <td><span class="label label-<?= isset($device->device_type) ? 'success' : 'warning' ?>">
                                <?= isset($device->device_type) ? $device->device_type : T_('Unknown')?>
                            </span></td>
                        </tr>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Is Phone')?></td>
                            <td><span class="label label-<?= isset($device->device_is_phone) ? 'success' : 'warning' ?>">
                                <?= isset($device->device_is_phone) ? ($device->device_is_phone === 'True'
                                    ? T_('Yes') : T_('No'))
                                    : T_('Unknown')?>
                            </span></td>
                        </tr>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Is SmartPhone')?></td>
                            <td><span class="label label-<?= isset($device->device_is_smartphone) ? 'success' : 'warning' ?>">
                                <?= isset($device->device_is_smartphone) ? ($device->device_is_smartphone === 'True'
                                    ? T_('Yes') : T_('No'))
                                    : T_('Unknown') ?>
                            </span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </li>
        <li class="span6">
            <div class="thumbnail">
                <div class="caption">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th><h4><?=T_('Device capabilities')?></h4></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Supports location')?></td>
                            <td><span class="label label-<?= $supported  ? 'success' : 'warning' ?>">
                            <?= $supported === null
                                ? T_('Unknown')
                                : ($supported ? T_('Yes') : T_('No'))?>
                            </span></td>
                        </tr>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Operating system')?></td>
                            <td><span class="label label-<?= isset($device->device_os_name) ? 'success' : 'warning' ?>">
                                <?= isset($device->device_os_name)
                                    ? $device->device_os_name . " ({$device->device_os_version})"
                                    : T_('Unknown')?>
                            </span></td>
                        </tr>
                        <tr>
                            <td class="no-wrap" style="min-width: 100px; width: 40%;"><?=T_('Last browser used')?></td>
                            <td><span class="label label-<?= isset($device->device_browser_name) ? 'success' : 'warning' ?>">
                                <?= isset($device->device_browser_name)
                                    ? $device->device_browser_name . " ({$device->device_browser_version})"
                                    : T_('Unknown') ?>
                            </span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </li>
    </ul>

<? } ?>
