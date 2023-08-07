<p class="lead">Hvor mange finner vi?</p>
<p>Det er mange grunner til at sporinger ikke fører til lokalisering. Hvis savnede er utenfor dekning,
    tom for batteri, velger å ikke klikke på lenken i SMSen, eller ikke klarer å aktivere deling av
    posisjon med nettleseren vil vi ikke klare å lokalisere telefonen.</p>
<? $days = 90;?>
<div class="row text-center no-wrap">
    <div id="no_response" class="span2"
         title="<?='Sporinger som ikke er blitt åpnet'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div  id="no_location" class="span2"
         title="<?='Åpnede sporinger uten lokasjon'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div  id="located" class="span2"
         title="<?='Sporinger med lokasjon'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div id="attainable" class="span2"
         title="<?='Sporinger som er blitt åpnet av savnede'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div class="span2 " style="height: 100px;"
         title="<?='Antall sporinger totalt'?>"
         rel="tooltip" data-placement="bottom">
            <div class="center" style="height: 100%;">
                <div style="height: 35%;"></div>
                <h3>
                    <span id="traces">0</span>
                    <img src="img/rescueme.png" width="16" height="16" class="img-rounded">
                </h3>
                <span class="small muted">Siste <?=$days?> dager</span>
            </div>
    </div>
</div>
<script>
    const gauges = [
        {id: 'no_response', label: 'Ingen respons'},
        {id: 'no_location', label:'Respons, ingen lokasjon'},
        {id: 'located', label: 'Lokalisert'},
        {id: 'attainable', label: 'Mulig å finne'},
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
    $(document.documentElement).find('[rel="tooltip"]').tooltip();
    $.getJSON('stats.php?type=trace&days=<?=$days?>', function(data) {
        const trace = data.trace;
        const total = trace.count.total.unique;
        const element = document.getElementById('traces');
        element.innerHTML = total;
        for (let g of gauges) {
            const value = trace.rates[g.config.id].unique;
            g.refresh(value * 100);
        }
    });
</script>