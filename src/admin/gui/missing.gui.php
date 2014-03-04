<?php
    use RescueMe\User;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;
    
    $id = input_get_int('id');

    $missing = Missing::getMissing($id);

    if($missing === false)
    {
        insert_alert('Ingen registrert');
    }
    else
    {        
    
        $positions = $missing->getPositions();
        $name = $missing->name;
        if(Operation::isOperationClosed($missing->op_id)) {
            $name .= " ("._("Closed").")";
        }

?>
<h3 class="pagetitle"><?= $name ?></h3>
<?php
        if(isset($_ROUTER['error'])) { ?>
	<div class="alert alert-error">
		<strong>En feil oppsto!</strong><br />
		<?= $_ROUTER['error'] ?>
	</div>
<?
        }
        
            $user_id = User::currentId();
            $format = Properties::get(Properties::MAP_DEFAULT_FORMAT, $user_id);
            if($missing->last_pos->timestamp>-1) {
                $position = format_pos($missing->last_pos, $format);
                $received = format_since($missing->last_pos->timestamp);
            } else {
                $received = "";
                $position = format_pos(null, $format);
            }
        
?>

<div class="infos clear-fix">
	<div class="info pull-left">
		<label class="label label-important">Siste posisjon</label> <?= $position ?>
	</div>
        <?php
        if (!empty($received)) { ?>
	<div class="info pull-left">
		<label class="label label-important">Posisjon mottatt</label> <?= $received ?>
	</div>
        <?php } ?>
	<div class="info pull-left">
		<label class="label label-important">Registrert savnet</label> <?= format_since($missing->reported) ?>
	</div>
</div>

<?php require_once(ADMIN_PATH_GUI.'missing.position.list.gui.php'); ?>
<div id="map" class="map"></div>
<div id="sidebar">
            <h4><?=_("Posisjoner &le; 1km")?></h4>
            <ul class="unstyled">
            <?php
	$i = 0;
    $displayed = false;
    
	foreach ($positions as $key=>$value) {
		if ($value->acc < 1000) { 
                    $displayed = true;
                    $timestamp = date('Y-m-d H:i:s', strtotime($value->timestamp));
                    ?>
			<li class="position clearfix well well-small" data-pan-to="<?= $i ?>">
                <time class="timeago" datetime="<?= $timestamp ?>"><?= format_since($value->timestamp) ?></time>
				<div class="noyaktighet"><?= $value->acc ?> m</div>
			</li>
		<?php
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
        <?php
	$i = 0;
        $displayed = false;
	foreach ($positions as $key=>$value) {
		if ($value->acc >= 1000) { 
                    $displayed = true;
                    $timestamp = date('Y-m-d H:i:s', strtotime($value->timestamp)); 
                    ?>
			<li class="position clearfix well well-small" data-pan-to="<?= $i ?>">
				<time class="timeago" datetime="<?= $timestamp ?>"><?= format_since($value->timestamp) ?></time>
				<div class="noyaktighet"><?= $value->acc ?> m</div>
			</li>
		<?php
		}
		$i++;
	}
        if (!$displayed) {
            echo '<li class="position clearfix well well-small">'._('Ingen').'</li>';
        }
	?>
	</ul>
</div>
<div class="infos clear-fix pull-left">
	<div class="info pull-left">
		<label class="label label-important">SMS sendt</label> <?= format_since($missing->sms_sent) ?>
	</div>
	<div class="info pull-left">
		<label class="label label-important">SMS levert</label> 
            <?php if ($missing->sms_delivery !== null)
                    echo format_since($missing->sms_delivery);
                else
                    echo _('Ukjent');
            ?>
	</div>
	<div class="info pull-left">
		<label class="label label-important">Respons</label> <?= format_since($missing->answered) ?>
	</div>
	<div class="info pull-left">
		<label class="label label-important">Sporingslenke</label> 
		<?= APP_URL."l/$missing->id/$missing->mobile"; ?>
	</div>
</div>

<? } ?>    
