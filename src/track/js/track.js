/*
 * Define 'namespaces'
 */

R = install;

/*
 * Accuracy of last location found
 */
var lc = 1000000;

/*
 * Get guery from track url
 */
var q = R.track;

/*
 * Implement location algorithm
 */
R.track.locate = function() {
    
    /*
     * Countdown timer id
     */
    var cID = 0;
    
    /**
     * Seconds until failure
     */
    var c = (q.wait/1000);
    
    /*
     * Image element
     */
    var i = null;
    
    /*
     * Feedback element
     */
    var f = document.getElementById("f");
    
    /*
     * Countdown element
     */    
    var s = document.getElementById("s");
    
    /*
     * Location element
     */
    var l = document.getElementById("l");
    
    if (navigator.geolocation) {
        navigator.geolocation.change(sl, se, sp, {
            maxWait:q.wait,       
            desiredAccuracy:q.acc});
    }
    else {
        f.innerHTML = "Lokalisering st&oslash;ttes ikke av din telefon.";
    }
    
    /*
     * Show position
     */
    function sp(p) {
        
        f.innerHTML = 'Har funnet deg med '+Math.ceil(p.coords.accuracy)+ ' m n&oslash;yaktighet... <br />'
                           + 'Søker etter mer nøyaktig posisjon, vent litt...';        
                   
        l.innerHTML = ps(p);
                   
        if (cID === 0) {
            i = document.createElement("img");
            i.src=R.app.url+"img/loading.gif"; //src of img attribute
            document.getElementById("i").appendChild(i); //append to body
            s.innerHTML = Math.floor(c / 60) +" m " + (c - Math.floor(c / 60) * 60) + " s";
            cID = setTimeout(dec, 1000);
        }
        
        // If the new position has improved by 10%, report it
        if (p.coords.accuracy + (lc*0.1) < lc) {
            lc = p.coords.accuracy;
            sp(p, false);
        }
    }
    
    /*
     * Decrement countdown
     */
    function dec() {
        if (c > 0) {
            c -= 1;
            s.innerHTML = s.innerHTML = Math.floor(c / 60) +" m " + (c - Math.floor(c / 60) * 60) + " s";
            cID = setTimeout(dec, 1000);
        }
        else {
            s.innerHTML = '';
            if (i !== null)
                document.getElementById("i").removeChild(i);
        }
    }

    /*
     * Show error to client
     */
    function se(e) {
        switch (e.code) {
            case e.PERMISSION_DENIED:
                f.innerHTML = "Du m&aring; bekrefte at du gir tillatelse til &aring; vise posisjon."
                break;
            case e.POSITION_UNAVAILABLE:
                f.innerHTML = "Posisjon er utilgjengelig."
                break;
            case e.TIMEOUT:
                f.innerHTML = "Du m&aring; bekrefte at du gir tillatelse til &aring; vise posisjon raskere."
                break;
            case e.UNKNOWN_ERROR:
                f.innerHTML = "Ukjent feil."
                break;
        }
    }
    
    /*
     * Show location results
     */
    function sl(p, u) {
        var y = p.coords;
        if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
            xhr = new XMLHttpRequest();
        }
        else {// code for IE6, IE5
             try {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
                xhr = false;
            }
        }
        
        l.innerHTML = ps(p);

        var url = R.app.url + "r/" + q.id + "/" + q.phone + "/" + (5) + "/" + y.latitude + "/" + y.longitude + "/" + y.accuracy + "/" + y.altitude;
        
        if (xhr !== false) {
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (u) {
                        f.innerHTML = xhr.responseText;
                        s.innerHTML = '';
                        clearTimeout(cID);
                        
                        if (i !== null)
                            document.getElementById("i").removeChild(i);
                    }
                }
            }

            xhr.open("GET", url, true);
            xhr.send();
        }
        
        // Fallback for those not supporting XMLhttprequest
        // Known: WP 7.8
        else if (y.accuracy < 300) {
            window.location = url;
        }
    }
    
    /**
     * Print position
     */
    function ps(p) {
        p = p.coords;
        return 'Din posisjon er: ' + p.longitude.toFixed(4) + 'E, ' + p.latitude.toFixed(4) + 'N';
    }
    
}

/**
 * Handle geolocation change.
 * 
 * @param function gls GeoLocation successfully found
 * @param function gle GeoLocation error occured
 * @param function gp Geolocation progress occured
 * @param object o Options: {maxWait, desiredAccuracy, timeout}
 * @returns void
 */
navigator.geolocation.change = function (gls, gle, gp, o) {
    
    var prev;
    var ec = 0;
    
    o = o || {};

    /*
     * Handle location checks
     */
    var cl = function (p) {
        prev = p;
        ++ec;
        // We ignore the first event unless it's the only one received because some devices seem to send a cached
        // location even when maxaimumAge is set to zero
        if ((p.coords.accuracy <= o.desiredAccuracy) && (ec > 0)) {
            clearTimeout(tID);
            navigator.geolocation.clearWatch(wID);
            fp(p);
        } else {
            gp(p);
        }
    }

    /*
     * Stop trying to get location fix.
     */
    var st = function () {
        navigator.geolocation.clearWatch(wID);
        fp(prev);
    }

    /*
     * Handle error events
     */
    var oe = function (e) {
        clearTimeout(tID);
        navigator.geolocation.clearWatch(wID);
        gle(e);
    }

    /*
     * Handle found position
     */
    var fp = function (p) {
        gls(p, true);
    }

    /*
     * Prepare options
     */
    if (!o.maxWait)            o.maxWait = q.wait; // Default 3 min
    if (!o.desiredAccuracy)    o.desiredAccuracy = q.acc; // Default 20 meters
    if (!o.timeout)            o.timeout = o.maxWait; // Default to maxWait

    o.maximumAge = q.age; // Accept that old positions
    o.enableHighAccuracy = true; // Force high accuracy (otherwise, why are you using this function?)

    var wID = navigator.geolocation.watchPosition(cl, oe, o);
    var tID = setTimeout(st, o.maxWait); // Set a timeout that will abandon the location loop
}