<div id="trace" class="infos clearfix pull-left">
    <div class="container-fluid pull-left">
        <div class="row-fluid <?=($collapsed ? 'hidden' : '')?>">
            <div class="span" 
                 title="<?=_('Når sporing ble opprettet')?>" 
                 rel="tooltip" data-placement="right">
                <b class="icon alarm icon-bullhorn"></b>
            </div>
            <div class="span" 
                 title="<?=_('Når SMS ble sendt')?>" rel="tooltip">
                <b class="icon sent icon-envelope"></b>
            </div>
            <div class="span" 
                 title="<?=_('Når SMS ble levert til telefonen')?>" rel="tooltip">
                <b class="icon delivered icon-eye-open"></b>
            </div>
            <div class="span" 
                 title="<?=_('Når sporingssiden ble lastet ned')?>" rel="tooltip">
                <b class="icon response icon-thumbs-up"></b>
            </div>
            <div class="span" 
                 title="<?=_('Når siste posisjon ble mottatt')?>" 
                 rel="tooltip" data-placement="left">
                <b class="icon located icon-flag"></b>
            </div>
        </div>
        <div class="row-fluid <?=($collapsed ? 'hidden' : '')?>">
            <div class="span">
                <?=_('Alerted')?>
            </div>
            <div class="span">
                <?=_('Sent')?>
            </div>
            <div class="span">
                <?=_('Delivered')?>
            </div>
            <div class="span">
                <?=_('Response')?>
            </div>
            <div class="span">
                <?=_('Located')?>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span" 
                 title="<?=$trace['alerted']['tooltip']?>" 
                 rel="tooltip" data-placement="right">
                <div class="trace-bar <?=$trace['alerted']['state']?>">
                    <b class="icon <?=($collapsed ? 'alarm' : '')?>"></b>
                </div>
            </div>
            <div class="span" 
                 title="<?=$trace['sent']['tooltip']?>" 
                 rel="tooltip">
                <div class="trace-bar <?=$trace['sent']['state']?>">
                    <b class="icon <?=($collapsed ? 'sent' : '')?>"></b>
                </div>
            </div>
            <div class="span" 
                 title="<?=$trace['delivered']['tooltip']?>" 
                 rel="tooltip">
                <div class="trace-bar <?=$trace['delivered']['state']?>">
                    <b class="icon <?=($collapsed ? 'delivered' : '')?>"></b>
                </div>
            </div>
            <div class="span" 
                 title="<?=$trace['response']['tooltip']?>" 
                 rel="tooltip">
                <div class="trace-bar <?=$trace['response']['state']?>">
                    <b class="icon <?=($collapsed ? 'response' : '')?>"></b>
                </div>
            </div>
            <div class="span" 
                 title="<?=$trace['located']['tooltip']?>"
                 rel="tooltip" data-placement="left">
                <div class="trace-bar <?=$trace['located']['state']?>">
                    <b class="icon <?=($collapsed ? 'located' : '')?>"></b>
                </div>
            </div>
        </div>
        <div class="row-fluid trace-details <?=($collapsed ? 'hidden' : '')?>">
            <div class="span">
                <small><?= $trace['alerted']['time'] ?></small>
            </div>
            <div class="span">
                <small><?= $trace['sent']['time'] ?></small>
            </div>
            <div class="span">
                <small><?= $trace['delivered']['time'] ?></small>
            </div>
            <div class="span">
                <small><?= $trace['response']['time'] ?></small>
            </div>
            <div class="span">
                <small><?= $trace['located']['time'] ?></small>
            </div>
        </div>    
    </div>
</div>