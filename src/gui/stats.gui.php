<p class="lead">Hvor mange finner vi?</p>
<p>Det er mange grunner til at sporinger ikke fører til lokalisering. Hvis savnede er utenfor dekning,
    tom for batteri, velger å ikke klikke på lenken i SMSen, eller ikke klarer å aktivere deling av
    posisjon med nettleseren vil vi ikke klare å lokalisere telefonen.</p>
<div class="row text-center no-wrap">
    <div class="span2" id="no_response"
         title="<?='Sporinger som ikke er blitt åpnet'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div class="span2" id="no_location"
         title="<?='Åpnede sporinger uten lokasjon'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div class="span2" id="total"
         title="<?='Sporinger med lokasjon'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div class="span2" id="attainable"
         title="<?='Sporinger som er blitt åpnet av savnede'?>"
         rel="tooltip" data-placement="bottom"></div>
    <div class="span2 " style="height: 100px;"
         title="<?='Antall sporinger totalt'?>"
         rel="tooltip" data-placement="bottom">
            <div class="center" style="height: 100%;">
                <div style="height: 25px;"></div>
                <h3 id="traces">0</h3>
                <p>
                    <i class="icon-envelope"></i> Sporinger
                </p>
            </div>
    </div>
</div>
<script>
    const gauges = [
        {id: 'no_response', label: 'Ingen respons'},
        {id: 'no_location', label:'Respons, ingen lokasjon'},
        {id: 'total', label: 'Lokalisert'},
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
    $.getJSON('stats.php', function(data) {
        const trace = data.trace;
        const total = trace.totals.all;
        const element = document.getElementById('traces');
        element.innerHTML = total;
        for (let g of gauges) {
            if(['attainable','total'].includes(g.config.id)) {
                const value = trace.rates[g.config.id].all;
                g.refresh(value * 100);
            } else {
                const stat = trace[g.config.id];
                const value = stat.all / total * 100;
                g.refresh(value);
            }
        }
    });
</script>