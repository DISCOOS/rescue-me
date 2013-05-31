<?php
$missing = new all_missing();
?>

<h3>Registrerte savnede</h3>
<ul class="unstyled">
<?php
foreach($missing->getAllMissing() as $id => $this_missing){
	$this_missing->getPositions();
	?>
	<li class="well well-small missing" id="<?= $id ?>">
		<div class="status pull-right">
			<label class="label label-inverse hidden-phone">Siste posisjon:</label>
			<?= $this_missing->last_pos->human?></div>
		<div class="name pull-left"><?= $this_missing->m_name ?></div>
		<div class="clearfix"></div>
	</li>
<?php
} ?>
</ul>


<h3>Tidligere savnede (lukkede saker)</h3>
<ul class="unstyled">
<?php
foreach($missing->getAllMissing('closed') as $id => $this_missing){
	$this_missing->getPositions();
	?>
	<li class="well well-small missing" id="<?= $id ?>">
		<div class="status pull-right">Sak lukket</div>
		<div class="name pull-left"><?= $this_missing->m_name ?></div>
	</li>
<?php
} ?>
</ul>