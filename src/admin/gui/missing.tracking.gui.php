<div id="tracking" class="infos clearfix pull-left">
    <div class="container-fluid pull-left">
        <div class="row-fluid">
            <div class="span" 
                 title="<?=_('Når sporing ble opprettet')?>" 
                 rel="tooltip" data-placement="right">
                <b class="icon alarm icon-bullhorn"></b>
                <br>
                <?=_('Alerted')?>
            </div>
            <div class="span" 
                 title="<?=_('Når SMS ble sendt')?>" rel="tooltip">
                <b class="icon sent icon-envelope"></b>
                <br>
                <?=_('Sent')?>
            </div>
            <div class="span" 
                 title="<?=_('Når SMS ble levert til telefonen')?>" rel="tooltip">
                <b class="icon delivered icon-eye-open"></b>
                <br>
                <?=_('Delivered')?>
            </div>
            <div class="span" 
                 title="<?=_('Når sporingssiden ble lastet ned')?>" rel="tooltip">
                <b class="icon response icon-thumbs-up"></b>
                <br>
                <?=_('Response')?>
            </div>
            <div class="span" 
                 title="<?=_('Når siste posisjon ble mottatt')?>" 
                 rel="tooltip" data-placement="left">
                <b class="icon located icon-flag"></b>
                <br>
                <?=_('Located')?>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span" 
                 title="<?=$tracking['alerted']['tooltip']?>" 
                 rel="tooltip" data-placement="right">
                <div class="tracking-bar <?=$tracking['alerted']['state']?>">
                    <b class="icon"></b>
                </div>
            </div>
            <div class="span" 
                 title="<?=$tracking['sent']['tooltip']?>" 
                 rel="tooltip">
                <div class="tracking-bar <?=$tracking['sent']['state']?>">
                    <b class="icon"></b>
                </div>
            </div>
            <div class="span" 
                 title="<?=$tracking['delivered']['tooltip']?>" 
                 rel="tooltip">
                <div class="tracking-bar <?=$tracking['delivered']['state']?>">
                    <b class="icon"></b>
                </div>
            </div>
            <div class="span" 
                 title="<?=$tracking['response']['tooltip']?>" 
                 rel="tooltip">
                <div class="tracking-bar <?=$tracking['response']['state']?>">
                    <b class="icon"></b>
                </div>
            </div>
            <div class="span" 
                 title="<?=$tracking['located']['tooltip']?>"
                 rel="tooltip" data-placement="left">
                <div class="tracking-bar <?=$tracking['located']['state']?>">
                    <b class="icon"></b>
                </div>
            </div>
        </div>
        <div class="row-fluid tracking-details">
            <div class="span">
                <small><?= $tracking['alerted']['time'] ?></small>
            </div>
            <div class="span">
                <small><?= $tracking['sent']['time'] ?></small>
            </div>
            <div class="span">
                <small><?= $tracking['delivered']['time'] ?></small>
            </div>
            <div class="span">
                <small><?= $tracking['response']['time'] ?></small>
            </div>
            <div class="span">
                <small><?= $tracking['located']['time'] ?></small>
            </div>
        </div>    
    </div>
</div>