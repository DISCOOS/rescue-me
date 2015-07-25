// Configure map
$.extend(R.map, config);

// Initialize map after document is loaded and ready
$(document).ready(function() {
    if(R.map.init(center)) {
        $.each(positions, function(i, p) {
            R.map.addPosition(p);
        });
        R.map.startFetchPositions();
    } else {
        $(R.map.id).html('<p class="map">'+ $.i18n.t("map.not_loaded") +'</p>');
    }
    setInterval(function(){R.updateTimes()}, 1000);
});