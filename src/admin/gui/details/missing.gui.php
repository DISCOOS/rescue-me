<?php

    use RescueMe\Missing;
    $missing = Missing::getMissing($_GET['id']);
    
    if($missing == false)
    {
?>
    <div class="alert alert-info">Ingen registrert</div>
<?php
    }
    else
    {
        
        $positions = $missing->getPositions();

?>
<h3 class="pagetitle">Savnet: <?= $missing->m_name ?></h3>
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
		<label class="label label-important">Meldt av</label> <?= $missing->mb_name ?> (<?= $missing->mb_mobile?>)
	</div>
	<div class="info pull-left">
		<label class="label label-important">Meldt savnet</label> <?= $missing->m_reported ?>
	</div>
</div>

<?php require_once(ADMIN_PATH_GUI.'list/positions.gui.php'); ?>
<div id="googleMap"></div>
<div id="sidebar">
	<h4>Posisjoner</h4>
	<strong>Nøyaktighet &lt; 1km</strong>
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
	<h4>Unøyaktige posisjoner</h4>
	<strong>Nøyaktighet &gt;= 1km</strong>
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
		<?= APP_URI.$missing->id.'-'.$missing->m_mobile; ?>
	</div>
</div>
<?php
    }
?>    
