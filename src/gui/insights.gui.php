<?
    if(!isset($days)) $days = 90;
    if(!isset($prefix)) $prefix = '';
    if(!isset($type)) $type = 'trace';
    if(!isset($name)) $name = 'ratios';
    if(!isset($user_id)) $user_id = 0; else $user_id = (int)$user_id;
?>
<div class="hidden-desktop"  style="height: 20px;"></div>
<div class="row no-wrap">
    <div class="stats span2">
        <div id="<?=$prefix?>s_no_response"
             title="<?=TRACES_NOT_OPENED_BY_RECEIVER?>"
             rel="tooltip" data-placement="top"></div>
        <div class="text-center visible-desktop" style="position: relative; top: -25px; width: 100%;">
            <svg id="<?=$prefix?>t_no_response" class="sparkline" width="145" height="30" stroke-width="1"></svg>
            <div class="sparkline-tooltip" hidden="true"></div>
        </div>
        <div class="text-center visible-phone" style="position: relative; top: 0; width: 100%;">
            <svg id="<?=$prefix?>t_no_response" class="sparkline" width="255" height="30" stroke-width="1"></svg>
            <div class="sparkline-tooltip" hidden="true"></div>
        </div>
    </div>
    <div class="stats span2">
        <div id="<?=$prefix?>s_no_location"
             title="<?=TRACES_WITHOUT_ANY_LOCATION?>"
             rel="tooltip" data-placement="top"></div>
        <div class="text-center visible-desktop" style="position: relative; top: -25px; width: 100%;">
            <svg id="<?=$prefix?>t_no_location" class="sparkline" width="145" height="30" stroke-width="1"></svg>
            <div class="sparkline-tooltip" hidden="true"></div>
        </div>
        <div class="text-center visible-phone" style="position: relative; top: 0; width: 100%;">
            <svg id="<?=$prefix?>t_no_location" class="sparkline" width="255" height="30" stroke-width="1"></svg>
            <div class="sparkline-tooltip" hidden="true"></div>
        </div>
    </div>
    <div class="stats span2">
        <div id="<?=$prefix?>s_located"
             title="<?=TRACES_WITH_AT_LEAST_ONE_LOCATION?>"
             rel="tooltip" data-placement="top"></div>
        <div class="text-center visible-desktop" style="position: relative; top: -25px; width: 100%;">
            <svg id="<?=$prefix?>t_located" class="sparkline" width="145" height="30" stroke-width="1"></svg>
            <span class="sparkline-tooltip" hidden="true"></span>
        </div>
        <div class="text-center visible-phone" style="position: relative; top: 0; width: 100%;">
            <svg id="<?=$prefix?>t_located" class="sparkline" width="255" height="30" stroke-width="1"></svg>
            <span class="sparkline-tooltip" hidden="true"></span>
        </div>
    </div>
    <div class="stats span2">
        <div id="<?=$prefix?>s_attainable"
             title="<?=TRACES_OPENED_BY_THE_RECEIVER?>"
             rel="tooltip" data-placement="top"></div>
        <div class="text-center visible-desktop" style="position: relative; top: -25px; width: 100%;">
            <svg id="<?=$prefix?>t_attainable" class="sparkline" width="145" height="30" stroke-width="1"></svg>
            <span class="sparkline-tooltip" hidden="true"></span>
        </div>
        <div class="text-center visible-phone" style="position: relative; top: 0; width: 100%;">
            <svg id="<?=$prefix?>t_attainable" class="sparkline" width="255" height="30" stroke-width="1"></svg>
            <span class="sparkline-tooltip" hidden="true"></span>
        </div>
    </div>
    <div class="span2 text-center" style="height: 100px;">
        <div class="center" style="height: 100%;">
            <div class="hidden-phone"  style="height: 57px;"></div>
            <h3>
                <span id="<?=$prefix?>s_total">0</span>
                <img src="<?=APP_URL?>img/rescueme.png" width="16" height="16" class="img-rounded">
            </h3>
            <span class="muted" style="position: relative; top: -20px; font-size: 10px;">
                <?=ucfirst(str_replace('{days}', $days, LAST_N_DAYS))?>
            </span>
        </div>
        <div class="text-center visible-desktop" style="position: relative; bottom: -30px; width: 100%;">
            <svg id="<?=$prefix?>t_total" class="sparkline" width="145" height="30" stroke-width="1"></svg>
            <span class="sparkline-tooltip" hidden="true"></span>
        </div>
        <div class="text-center visible-phone" style="position: relative; bottom: 35px; width: 100%;">
            <svg id="<?=$prefix?>t_total" class="sparkline" width="255" height="30" stroke-width="1"></svg>
            <span class="sparkline-tooltip" hidden="true"></span>
        </div>
    </div>
</div>
<?if(isset($user_id)){?>
<div class="hidden-desktop"  style="height: 40px;"></div>
<div id="<?=$prefix?>s_rank" class="text-center">
    <p><?=FETCHING_RANKINGS?>...</p>
</div>
<div class="hidden-mobile"  style="height: 20px;"></div>
<?}?>
<style type="text/css" scoped>
    .sparkline {
        align-self: start;
        stroke: rgb(16, 198, 137);
        fill: rgba(16, 198, 137, .3);
        animation: fadeIn 5s;
        -webkit-animation: fadeIn 3s;
        -moz-animation: fadeIn 3s;
        -o-animation: fadeIn 3s;
        -ms-animation: fadeIn 3s;
    }

    .sparkline-empty {
        stroke: rgb(221, 221, 221);
        fill: rgba(221, 221, 221, .3);

    }

    .sparkline-tooltip {
        position: absolute;
        background: rgba(0, 0, 0, .7);
        color: #fff;
        padding: 2px 5px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 9999;
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    @-moz-keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    @-webkit-keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    @-o-keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    @-ms-keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    /* Landscape phone to portrait tablet */
    @media (max-width: 767px) {
        .stats {
            height: 240px;
        }
    }

    /* Landscape phones and down */
    @media (max-width: 480px) {
        .stats {
            height: 240px;
        }
    }

</style>
<script type="application/javascript">
    const <?=$prefix?>gauges = [
        {id: '<?=$prefix?>s_no_response', label: '<?=NO_RESPONSE?>'},
        {id: '<?=$prefix?>s_no_location', label:'<?=RESPONSE_NO_LOCATION?>'},
        {id: '<?=$prefix?>s_located', label: '<?=LOCATED?>'},
        {id: '<?=$prefix?>s_attainable', label: '<?=POSSIBLE_TO_LOCATE?>'},
    ].map((gauge) => new JustGage({
            id: gauge.id, // the id of the html element
            value: 0.0,
            decimals: 0,
            hideMinMax: true,
            label: gauge.label,
            levelColors: ['#10C689'],
            gaugeWidthScale: 0.6,
            <?=$name==='ratios' ? 'symbol: "%"' : ''?>
    }));

    function <?=$prefix?>getStats(type, name, days, user_id) {
        const ratios = <?=$name==='ratios' ? 'true' : 'false' ?>;
        const <?=$prefix?>StatsUrl = '<?=APP_URL?>stats.php?type='+type+'&days='+days+'&user_id='+user_id;
        $.getJSON(<?=$prefix?>StatsUrl, function(data) {
            const stats = data.trace;
            const total = Number(stats.counts.total);
            const element = document.getElementById('<?=$prefix?>s_total');
            element.innerHTML = total;
            for (let g of <?=$prefix?>gauges) {
                const state = g.config.id.replace(/^<?=$prefix?>s_/,'');
                const value = stats[name][state];
                g.refresh(ratios ? value * 100 : value, ratios ? 100 : total);
            }
        });
    }

    <?=$prefix?>getStats('<?=$type?>', '<?=$name?>', <?=$days?>, <?=$user_id?>);

</script>

<script type="application/javascript">
    const field = '<?=$name==='ratios' ? 'cum' : 'daily' ?>';
    const <?=$prefix?>sparklines = {
        no_response: {key: field, label: '<?=NO_RESPONSE?>'},
        no_location: {key: field, label: '<?=RESPONSE_NO_LOCATION?>'},
        located: {key: field, label: '<?=LOCATED?>'},
        attainable: {key: field, label: '<?=POSSIBLE_TO_LOCATE?>'},
        total: {key: 'cum', label: '<?=TRACES?>'},
    };

        function <?=$prefix?>getTrends(type, name, days, user_id) {
            const ratios = <?=$name==='ratios' ? 'true' : 'false' ?>;
            const url = '<?=APP_URL?>trends.php?type='+type+'&days='+days+'&user_id='+user_id;
            function findClosest(target, tagName) {
            if (target.tagName === tagName) {
                return target;
            }

            while ((target = target.parentNode)) {
                if (target.tagName === tagName) {
                    break;
                }
            }

            return target;
        }
        const options = {
            onmousemove(event, datapoint) {
                const svg = findClosest(event.target, "svg");
                const state = svg.id.replace(/^<?=$prefix?>t_/, '');
                const tooltip = svg.nextElementSibling;
                if(datapoint.date === undefined) {
                    tooltip.textContent = "<?=NOT_FOUND?>";
                } else {
                    const date = (new Date(datapoint.date)).toLocaleDateString();
                    const v = ratios && state !== 'total'
                        ? `${datapoint.value}%`
                        : `${datapoint.value}`;
                    tooltip.textContent = `${date}: ${v} ${<?=$prefix?>sparklines[state].label} (${datapoint.daily})`;
                }
                tooltip.hidden = false;
                tooltip.style.top = `${event.offsetY}px`;
                tooltip.style.left = `${event.offsetX + 20}px`;
            },

            onmouseout() {
                const svg = findClosest(event.target, "svg");
                const tooltip = svg.nextElementSibling;

                tooltip.hidden = true;
            }
        };

        $.getJSON(url, function (data) {
            const trends = data.trace;
            document.querySelectorAll(".sparkline").forEach(function (svg) {
                const state = svg.id.replace(/^<?=$prefix?>t_/, '');
                const total = (state === 'total');
                const key = <?=$prefix?>sparklines[state].key;
                const counts = trends.counts[state];
                const ratios = trends[name][state];
                const trend = total ? counts : ratios;
                let series = trend.map((e) => e[key]).map(Number);
                const length = series.reduce((a, b) => a + b);
                if (length === 0) {
                    svg.classList.add('sparkline-empty');
                    series = [1, 1];
                } else {
                    let prev = series.find((v) => v > 0);
                    series = trend.map((e) => {
                        let v = Number(e[key]);
                        let d = Number(e.daily);
                        if(ratios && !total) {
                            v = Math.round(v * 100);
                            d = Math.round(d * 100);
                            if (v > 0) {
                                prev = v;
                            } else {
                                v = prev;
                            }
                        }
                        return {
                            name: key,
                            date: e['date'],
                            value: v,
                            daily: d,
                        };
                    });
                }
                Sparkline.sparkline(svg, series, options);
            });
        });
    }

    <?=$prefix?>getTrends('<?=$type?>', '<?=$name?>', <?=$days?>, <?=$user_id?>);


</script>

<script type="application/javascript">
    function <?=$prefix?>getRanks(type, name, days, user_id) {
        const <?=$prefix?>RankUrl = '<?=APP_URL?>ranks.php?type='+type+'&days='+days+'&user_id='+user_id;
        $.getJSON(<?=$prefix?>RankUrl, function (data) {
            const ranks = data.trace;
            const user = ranks.users[user_id];
            const element = document.getElementById('<?=$prefix?>s_rank');
            if(user === undefined) {
                const best = ranks.users[ranks.best];
                if(user_id > 0 || best === undefined) {
                    $(element).html(
                        "<p>"+R.interpolate(i18n.t("insights.no_traces_created"), {
                            "days": "<b>" + R.interpolate(i18n.t("insights.last_n_days"), {"days": days}) + "</b>"
                        }) +".</p>"
                    );
                } else {
                    const percent = Math.round(best.success * 100, 0);
                    $(element).html(
                        "<p>"+R.interpolate(i18n.t("insights.best_operator_is"), {
                            "success": "<b>" + percent + "%</b>"})
                        + "</p>"
                    );
                }
            } else {
                const best = Math.round(ranks.users[ranks.best].success * 100, 0);
                const rank = Math.round(user.better * 100, 0);
                $(element).html(
                    user.rank === 1
                        ? ("<p>" + R.interpolate(i18n.t("insights.are_best_operator"), {
                            "success": "<b>" + Math.round(user.success * 100, 0) + "%</b>",
                            "operator": "<b>" + i18n.t("insights.the_best") + "</b>",
                            "days": "<b>" + R.interpolate(i18n.t("insights.last_n_days"), {"days": days}) + "</b>"})
                        + "</p>")
                        : ("<p>" + R.interpolate(i18n.t("insights.nth_best_operator"), {
                            "success": "<b>" + Math.round(user.success * 100, 0) + "%</b>",
                            "days": "<b>" + R.interpolate(i18n.t("insights.last_n_days"), {"days": days}) + "</b>",
                            "lesser": "<b>" + rank  + "%</b>"})
                        + "</p><p>" + R.interpolate(i18n.t("insights.best_operator_is"), {
                                "success": "<b>" + best + "%</b>"})
                        + "</p>")

                );
            }

        });
    }

    <?=$prefix?>getRanks('<?=$type?>', '<?=$name?>', <?=$days?>, <?=$user_id?>);


</script>