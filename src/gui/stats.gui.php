<?
    if(!isset($days)) $days = 90;
    if(!isset($set)) $set = 'all';
    if(!isset($type)) $type = 'trace';
    if(!isset($prefix)) $prefix = '';
    if(!isset($user_id)) $user_id = 0; else $user_id = (int)$user_id;
?>
<div class="row text-center no-wrap">
    <div id="<?=$prefix?>no_response" class="span2"
         title="<?='Sporinger som ikke er blitt åpnet'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div  id="<?=$prefix?>no_location" class="span2"
         title="<?='Åpnede sporinger uten lokasjon'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div  id="<?=$prefix?>located" class="span2"
         title="<?='Sporinger med lokasjon'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div id="<?=$prefix?>attainable" class="span2"
         title="<?='Sporinger som er blitt åpnet av savnede'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div class="span2 " style="height: 100px;">
            <div class="center" style="height: 100%;">
                <div style="height: 35%;"></div>
                <h3>
                    <span id="<?=$prefix?>traces">0</span>
                    <img src="<?=APP_URL?>img/rescueme.png" width="16" height="16" class="img-rounded">
                </h3>
                <span class="small muted">Siste <?=$days?> dager</span>
            </div>
    </div>
</div>
<script>
    const <?=$prefix?>gauges = [
        {id: '<?=$prefix?>no_response', label: 'Ingen respons'},
        {id: '<?=$prefix?>no_location', label:'Respons, ingen lokasjon'},
        {id: '<?=$prefix?>located', label: 'Lokalisert'},
        {id: '<?=$prefix?>attainable', label: 'Mulig å finne'},
    ].map((gauge) => new JustGage({
            id: gauge.id, // the id of the html element
            value: 0.0,
            decimals: 1,
            symbol: '%',
            hideMinMax: true,
            label: gauge.label,
            levelColors: ['#10C689'],
            gaugeWidthScale: 0.6
    }));
    const <?=$prefix?>url = '<?=APP_URL?>stats.php?type=<?=$type?>&days=<?=$days?>&user_id=<?=$user_id?>';
    $.getJSON(<?=$prefix?>url, function(data) {
        const trace = data.trace;
        const total = trace.count.total['<?=$set?>'];
        const element = document.getElementById('<?=$prefix?>traces');
        element.innerHTML = total;
        for (let g of <?=$prefix?>gauges) {
            const state = g.config.id.replace(/^<?=$prefix?>/,'');
            const value = trace.rates[state]['<?=$set?>'];
            g.refresh(value * 100);
        }
    });
</script>