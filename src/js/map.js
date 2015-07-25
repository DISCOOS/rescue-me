// Get RescueMe "namespace" (R is required)
R = window.R;

// Define map "namespace"
R.map = {};

/**
 * Get zoom from accuracy
 * @param acc
 * @returns {number}
 */
R.map.getZoom = function (acc) {
    if (acc >= 0 && acc < 200)
        return 16;
    else if (acc > 0 && acc < 750)
        return 15;
    else if (acc > 0 && acc < 1200)
        return 14;
    else if (acc > 0 && acc < 1500)
        return 13;
    else
        return 11;
};

/**
 * Get position information
 * @param p Position
 * @param format Position format
 * @returns {string}
 */
R.map.getPositionInfo = function(p, format) {
    return '<b>'+format+':</b> ' + p.text + '<br /><hr />' +
        '<b>'+i18n.t("map.format.ddg")+':</b> ' + p.lat + ', ' + p.lon + '<br /><hr />' +
        '<b>'+$.i18n.t("map.accuracy")+':</b> ± ' + p.acc + ' meter<br />' +
        '<b>'+$.i18n.t("map.timestamp")+':</b> ' + R.format_since(p.timestamp);
};

/**
 * Add to position list
 * @param p
 * @param markerNo
 */
R.map.addToList = function(p, markerNo) {
    // Create position entry element
    var li = $("<li/>",
        {"class": "position text-left clearfix well well-small",
        "id": "position-" + markerNo,
        "data-pan-to": markerNo}
    );

    var span = $("<span/>").html(p.simple + ' &plusmn; ' + p.acc + ' m');
    var time = $("<time/>", {"datetime": p.timestamp}).text(R.format_since(p.timestamp));

    span.append(time);
    li.append(span);

    if (p.acc <= 1000) {
        $('#lt1km').prepend(li);
        $('#lt1kmtitle').show();
        $('#nopos').hide();
    }
    else {
        $('#ge1km').prepend(li);
        $('#ge1kmtitle').show();
        $('#nopos').hide();
    }

    // Bind position in list with position in map
    R.map.bindPosition(li, markerNo);

}

/**
 * Start fetchin positions from server continuously using 'long fetch'
 */
R.map.startFetchPositions = function() {
    R.longFetch(
        R.map.config.url,
        R.map.ajaxAddPos,
        {num: R.map.getPositionCount()},
        180000,
        R.map.startFetchPositions);
};