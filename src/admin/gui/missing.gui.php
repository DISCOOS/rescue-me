<?php
    use RescueMe\Operation;
    $operation = Operation::getOperation($_GET['id']);
    $missings = $operation->getAllMissing();
    $missing = current($missings);
    
    if($missing == false)
    {
        insert_alert('Ingen registrert');
    }
    else
    {        
        $positions = $missing->getPositions();

?>
<h3 class="pagetitle"><?= MISSING_PERSON ?>: <?= $missing->m_name ?></h3>
<?php
        if(isset($_ROUTER['message'])) { ?>
	<div class="alert alert-error">
		<strong>En feil oppsto!</strong><br />
		<?= $_ROUTER['message'] ?>
	</div>
<?
        }
?>

<div class="infos clear-fix">
	<div class="info pull-left">
		<label class="label label-important">Sist posisjonert</label> <?= $missing->last_pos->human?>
	</div>
	<div class="info pull-left">
		<label class="label label-important">Meldt savnet</label> <?= $missing->m_reported ?>
	</div>
</div>

<?php require_once(ADMIN_PATH_GUI.'missing.position.list.gui.php'); ?>
<div id="googleMap"></div>
<div id="sidebar">
	<h4>Posisjoner &lt; 1km</h4>
	<ul class="unstyled">
	<?php
	$i = 0;
	foreach ($positions as $key=>$value) {
		if ($value->acc < 1000) {?>
			<li class="position clearfix well well-small" data-pan-to="<?= $i ?>">
				<time class="timeago" datetime="<?= date('Y-m-d H:i:s', $value->timestamp)?>"><?= $value->human ?></time>
				<div class="noyaktighet"><?= $value->acc ?> m</div>
			</li>
		<?php
		}
		$i++;
	} ?>
	</ul>
	<h4>Posisjoner &ge; 1km</h4>
	<ul class="unstyled">
	<?php
	$i = 0;
	foreach ($positions as $key=>$value) {
		if ($value->acc >= 1000) {?>
			<li class="position clearfix well well-small" data-pan-to="<?= $i ?>">
				<time class="timeago" datetime="<?= date('Y-m-d H:i:s', $value->timestamp)?>"><?= date('d.M H:i:s', $value->timestamp) ?></time>
				<div class="noyaktighet"><?= $value->acc ?> m</div>
			</li>
		<?php
		}
		$i++;
	}
	?>
	</ul>
</div>
<div class="infos clear-fix">
	<div class="info pull-left">
		<label class="label label-important">Sporingslenke den savnede bruker</label> 
		<?= APP_URL.'l/'.$missing->id.'/'.$missing->m_mobile; ?>
	</div>
</div>
<?php
    }
?>    
