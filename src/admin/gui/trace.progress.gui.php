<div class="trace <?=($collapsed ? '' : 'expanded')?>">
    <div class="row <?=($collapsed ? 'hidden' : '')?>">
        <div class="span">
            <b class="icon alarm icon-bullhorn"
               title="<?=T_('When trace was registered')?>"
               rel="tooltip" data-placement="right"></b>
        </div>
        <div class="span">
            <b class="icon sent icon-envelope"
               title="<?=T_('When SMS was sent')?>"
               rel="tooltip"></b>
        </div>
        <div class="span">
            <b class="icon delivered icon-eye-open"
               title="<?=T_('When SMS was delivered to mobile')?>"
               rel="tooltip"></b>
        </div>
        <div class="span">
            <b class="icon response icon-thumbs-up"
               title="<?=T_('When location script was downloaded')?>"
               rel="tooltip"></b>
        </div>
        <div class="span">
            <b class="icon located icon-flag"
               title="<?=T_('When last location was received')?>"
               rel="tooltip" data-placement="left"></b>
        </div>
    </div>
    <div class="row <?=($collapsed ? 'hidden' : '')?>">
        <div class="span">
            <?=T_('Alerted')?>
        </div>
        <div class="span">
            <?=T_('Sent')?>
        </div>
        <div class="span">
            <?=T_('Delivered')?>
        </div>
        <div class="span">
            <?=T_('Responded')?>
        </div>
        <div class="span">
            <?=T_('Located')?>
        </div>
    </div>
    <div class="row">
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
             title="<?=$trace['responded']['tooltip']?>"
             rel="tooltip">
            <div class="trace-bar <?=$trace['responded']['state']?>">
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
    <div class="row trace-details <?=($collapsed ? 'hidden' : '')?>">
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
            <time datetime="<?=$trace['responded']['timestamp']?>"><?= $trace['responded']['time'] ?></time>
        </div>
        <div class="span">
            <time datetime="<?=$trace['located']['timestamp']?>"><?= $trace['located']['time'] ?></time>
        </div>
    </div>
</div>
