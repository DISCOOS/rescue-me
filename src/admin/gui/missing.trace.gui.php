<div class="trace <?=($collapsed ? '' : 'expanded')?>">
    <div class="container-fluid pull-left">
        <div class="row-fluid <?=($collapsed ? 'hidden' : '')?>">
            <div class="span" 
                 title="<?=T_('When trace was registered')?>" 
                 rel="tooltip" data-placement="right">
                <b class="icon alarm icon-bullhorn"></b>
            </div>
            <div class="span" 
                 title="<?=T_('When SMS was sent')?>" rel="tooltip">
                <b class="icon sent icon-envelope"></b>
            </div>
            <div class="span" 
                 title="<?=T_('When SMS was delivered to mobile')?>" rel="tooltip">
                <b class="icon delivered icon-eye-open"></b>
            </div>
            <div class="span" 
                 title="<?=T_('When location script was downloaded')?>" rel="tooltip">
                <b class="icon response icon-thumbs-up"></b>
            </div>
            <div class="span" 
                 title="<?=T_('When last location was received')?>" 
                 rel="tooltip" data-placement="left">
                <b class="icon located icon-flag"></b>
            </div>
        </div>
        <div class="row-fluid <?=($collapsed ? 'hidden' : '')?>">
            <div class="span">
                <?=ALERTED?>
            </div>
            <div class="span">
                <?=SENT?>
            </div>
            <div class="span">
                <?=DELIVERED?>
            </div>
            <div class="span">
                <?=RESPONSE?>
            </div>
            <div class="span">
                <?=LOCATED?>
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
                <time datetime="<?=$trace['alerted']['timestamp']?>"><?= $trace['alerted']['time'] ?></time>
            </div>
            <div class="span">
                <time datetime="<?=$trace['sent']['timestamp']?>"><?= $trace['sent']['time'] ?></time>
            </div>
            <div class="span">
                <time datetime="<?=$trace['delivered']['timestamp']?>"><?= $trace['delivered']['time'] ?></time>
            </div>
            <div class="span">
                <time datetime="<?=$trace['response']['timestamp']?>"><?= $trace['response']['time'] ?></time>
            </div>
            <div class="span">
                <time datetime="<?=$trace['located']['timestamp']?>"><?= $trace['located']['time'] ?></time>
            </div>
        </div>    
    </div>
</div>
