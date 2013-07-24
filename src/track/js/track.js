R.track = {};
var lastAcc = 1000000;
R.track.locate = function() {
    var x = document.getElementById("feedback");
    if (navigator.geolocation) {
        navigator.geolocation.getAccurateCurrentPosition(showPosition, showError, showProgress, {
            maxWait:30000,            // 30 sek
            desiredAccuracy:100});    // 100 m
    }
    else {
        x.innerHTML = "Lokalisering st&oslash;ttes ikke av din telefon.";
    }

    function showProgress(position) {
        x.innerHTML = 'Har funnet deg med '+position.coords.accuracy+ ' m n&oslash;yaktighet... <br />'
                           + 'Søker etter mer nøyaktig posisjon, vent litt...';
        if (position.coords.accuracy + 50 < lastAcc) {
            lastAcc = position.coords.accuracy;
            showPosition(position, false);
        }
    }

    function showError(error) {
        switch (error.code) {
            case error.PERMISSION_DENIED:
                x.innerHTML = "Du m&aring; bekrefte at du gir tillatelse til &aring; vise posisjon."
                break;
            case error.POSITION_UNAVAILABLE:
                x.innerHTML = "Posisjon er utilgjengelig."
                break;
            case error.TIMEOUT:
                x.innerHTML = "Du m&aring; bekrefte at du gir tillatelse til &aring; vise posisjon raskere."
                break;
            case error.UNKNOWN_ERROR:
                x.innerHTML = "Ukjent feil."
                break;
        }
    }

    function showPosition(position, updateHTML) {
        var y = position.coords;
        if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else {// code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }

        var query = R.toQuery(document.scripts.namedItem("track").src);

        console.log(query);

        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                if (updateHTML)
                    x.innerHTML = xmlhttp.responseText;
            }
        }

        var url = R.app.url + "r/" + query.id + "/" + query.country + "/" + query.phone + "/" + (5) + "/" + y.latitude + "/" + y.longitude + "/" + y.accuracy + "/" + y.altitude;
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    }
}

navigator.geolocation.getAccurateCurrentPosition = function (geolocationSuccess, geolocationError, geoprogress, options) {
    var lastCheckedPosition;
    var locationEventCount = 0;
    
    options = options || {};

    var checkLocation = function (position) {
        lastCheckedPosition = position;
        ++locationEventCount;
        // We ignore the first event unless it's the only one received because some devices seem to send a cached
        // location even when maxaimumAge is set to zero
        if ((position.coords.accuracy <= options.desiredAccuracy) && (locationEventCount > 0)) {
            clearTimeout(timerID);
            navigator.geolocation.clearWatch(watchID);
            foundPosition(position);
        } else {
            geoprogress(position);
        }
    }

    var stopTrying = function () {
        navigator.geolocation.clearWatch(watchID);
        foundPosition(lastCheckedPosition);
    }

    var onError = function (error) {
        clearTimeout(timerID);
        navigator.geolocation.clearWatch(watchID);
        geolocationError(error);
    }

    var foundPosition = function (position) {
        geolocationSuccess(position, true);
    }

    if (!options.maxWait)            options.maxWait = 10000; // Default 10 seconds
    if (!options.desiredAccuracy)    options.desiredAccuracy = 20; // Default 20 meters
    if (!options.timeout)            options.timeout = options.maxWait; // Default to maxWait

    options.maximumAge = 0; // Force current locations only
    options.enableHighAccuracy = true; // Force high accuracy (otherwise, why are you using this function?)

    var watchID = navigator.geolocation.watchPosition(checkLocation, onError, options);
    var timerID = setTimeout(stopTrying, options.maxWait); // Set a timeout that will abandon the location loop
}