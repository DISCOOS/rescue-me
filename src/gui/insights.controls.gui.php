<?
    if(!isset($days)) $days = 90;
    if(!isset($prefix)) $prefix = '';
    if(!isset($type)) $type = 'trace';
    if(!isset($name)) $name = 'ratios';
    if(!isset($user_id)) $user_id = 0; else $user_id = (int)$user_id;
?>
<div class="hidden-desktop"  style="height: 40px;"></div>
<table class="table">
    <tr>
        <td>
            <div class ="pull-left"><?=$name==='ratios' ? RATIOS_PER_STATE: TOTALS_PER_STATE?></div>
        </td>
        <td>
            <div class="btn-toolbar pull-right" style="margin-top: 0; margin-bottom: 0;">
                <div class="btn-group" data-toggle="buttons-radio">
                    <a type="button" class="btn btn-small <?=$days===180?'active':''?>" href="<?="?name=$name&days=180"?>">180d</a>
                    <a type="button" class="btn btn-small <?=$days===90?'active':''?>" href="<?="?name=$name&days=90"?>">90d</a>
                    <a type="button" class="btn btn-small <?=$days===60?'active':''?>" href="<?="?name=$name&days=60"?>">60d</a>
                    <a type="button" class="btn btn-small <?=$days===30?'active':''?>" href="<?="?name=$name&days=30"?>">30d</a>
                </div>
                <div class="btn-group" data-toggle="buttons-radio">
                    <a type="button" class="btn btn-small <?=$name==='ratios'?'active':''?>" href="<?="?name=ratios&days=$days"?>"><?=RATIOS?></a>
                    <a type="button" class="btn btn-small <?=$name==='counts'?'active':''?>" href="<?="?name=counts&days=$days"?>"><?=TOTALS?></a>
                </div>
            </div>
        </td>
    </tr>
</table>