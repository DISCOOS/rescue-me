/*
 * Define 'namespaces'
 */
R = install;

/*
 * Get guery from track url
 */
var q = R.track;


/**
 * Document reference
 */
var d = document;



/*
 * Define reference
 */
var ngl = navigator.geolocation;


/**
 * Get element
 * @param id Element id
 */
function get(id) {
    return d.getElementById(id);
}


/*
 * Implement location algorithm
 */
R.track.locate = function() {
    
    /*
     * Countdown timer id
     */
    var cID = 0;
    
    /**
     * Seconds until failure in seconds
     */
    var w = (q.wait/1000);
    
    /*
     * Image element
     */
    var i = get("i");
    
    /*
     * Feedback element
     */
    var f = get("f");
    
    /*
     * Countdown element
     */    
    var s = get("s");
    
    /*
     * Location element
     */
    var l = get("l");
    
    /* ===================================================
     *  Register geolocation callback
     * =================================================== */
    if(ngl) {
        
        setTimeout(function() {R.track.change(rp, se, sp, q);}, q.delay ? 3000 : 0);
        
        // Register progress indicator
        var im = d.createElement("img");
        im.src = R.app.url+"img/loading.gif";
        i.appendChild(im);
        s.innerHTML = pt(w);
        cID = setTimeout(cd, 1000);
        
    }
    else {
        f.innerHTML = "Lokalisering st&oslash;ttes ikke av din telefon.";
    }
    
    
    
    /*
     * Show position
     * @param p position coordinates
     * @param a position age
     */
    function sp(c, a) {
        
        var m = 'Fant posisjon med '+Math.ceil(c.accuracy)+ ' m n&oslash;yaktighet... <br />';
        
        // Tell client to check if GPS is off?
        if(a > q.age) m += 'Posisjon er for gammel, sjekk om GPS er påslått!';
        
        // Continue listen for position changes    
        m += 'Søker etter mer nøyaktig posisjon, vent litt...';
            
        // Update views
        f.innerHTML = m;        
        l.innerHTML = ps(c);
        
    }
    
    /*
     * Decrement countdown
     */
    function cd() {
        if (w > 0) {
            s.innerHTML = pt(--w);
            cID = setTimeout(cd, 1000);
        }
        else {
            s.innerHTML = '';
            i.innerHTML = '';
        }
    }

    /*
     * Show error to client
     */
    function se(e) {
        switch (e.code) {
            case e.PERMISSION_DENIED:
                f.innerHTML = "Du m&aring; sl&aring p&aring tilgang til &aring dele posisjonen din (systeminnstilling).";
                break;
            case e.POSITION_UNAVAILABLE:
                f.innerHTML = "Posisjon er utilgjengelig.";
                break;
            case e.TIMEOUT:
                f.innerHTML = "Du m&aring; bekrefte at du gir tillatelse til &aring; vise posisjon raskere.";
                break;
            case e.UNKNOWN_ERROR:
                f.innerHTML = "Ukjent feil.";
                break;
        }
    }
    
    /*
     * Report aquirred location to server
     * @param c position coordinates
     * @param u update view flag
     */
    function rp(c, u) {
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
        
        l.innerHTML = ps(c);
        
        var url = R.app.url + "r/" + q.id + "/" + q.phone + "/" + c.latitude + "/" + c.longitude + "/" + c.accuracy + "/" + c.altitude;
        
        if (xhr !== false) {
            
            xhr.onreadystatechange = function() {
                
                if (xhr.readyState === 4 && xhr.status === 200) {
                    
                    // Update message with response text?
                    if(u) {
                        f.innerHTML = xhr.responseText;
                        s.innerHTML = '';
                        i.innerHTML = '';
                        clearTimeout(cID);                        
                    }
                }
            }

            xhr.open("GET", url, true);
            xhr.send();                
            
        }
        
        // Fallback for those not supporting XMLhttprequest
        // Known: WP 7.8
        else if (c.accuracy < 300) {
            window.location = url;
        }
    }
    
    /**
     * Print position coordinates
     * @param c position coordinates
     */
    function ps(c) {
        var l = c.longitude.toFixed(4) + 'E ' + c.latitude.toFixed(4) + 'N';
        var u = q.id + '|' + q.phone + '|' + l + '|' + q.name;
        var ua = navigator.userAgent.toLowerCase();
        var d = (ua.indexOf("iphone") > -1 || ua.indexOf("ipad") > -1) ? ';' : '?';
        return 'Posisjon: <b>' + l + '</b> (<a href="sms:' + q.to + d + 'body=' + u + '">sms</a>)';
    }
    
    
    /**
     * Print time in minutes and seconds
     * @param t time in seconds
     */
    function pt(t) {
        return Math.floor(t / 60) +" m " + (t - Math.floor(t / 60) * 60) + " s";
    }
    
};

/**
 * Handle geolocation change.
 * 
 * @param gf GeoLocation found
 * @param ge GeoLocation error occured
 * @param gp Geolocation progress occured
 * @param o Geolocation Options: {
 *                      wait: maximum time to wait for position, 
 *                      age: only accept positions younger than this, 
 *                      acc: continue to wait for position until desired position is aquired
 *                  }
 * @returns void
 */
R.track.change = function (gf, ge, gp, o) {

    var lc;

    var la = Infinity;

    o = o || {};

    /*
     * Handle location checks
     */
    var hc = function (p) {
        
        // Used on timeout
        lc = p.coords;
        
        var q = lc.accuracy;
        var a = Date.now() - p.timestamp;
        
        // We ignore the first event unless it's the only one 
        // received because some devices seem to send a cached
        // location even when maxaimumAge is set to zero!
        if((q <= o.acc) && a <= o.age) {
            gf(p.coords, true);
            clearTimeout(tID);
            ngl.clearWatch(wID);
        // If the new position has improved by 10%, report it
        } else if (q < la * 0.9) {
            gf(p.coords, false);
            la = q;            
        } else {
            gp(lc, a);
        }
    };

    /*
     * Handle error events
     */
    var he = function (e) {
        clearTimeout(tID);
        ngl.clearWatch(wID);
        ge(e);
    };

    /*
     * Handle found position
     */
    var fp = function (p) {
        gf(p.coords, true);
    };
    
    /*
     * Stop trying to get location fix.
     */
    var st = function () {
        ngl.clearWatch(wID);
        fp(lc);
    };

    /*
     * Prepare options
     */
    o.timeout = o.wait; // Maximum time allowed time to wait for position (milliseconds)
    o.maximumAge = o.age; // Accept positions younger than this (milliseconds)
    o.enableHighAccuracy = true; // Force high accuracy (otherwise, why are you using this function?)

    var wID = ngl.watchPosition(hc, he, o);

    f.innerHTML = 'Beregner posisjon...';

    var tID = setTimeout(st, o.wait); // Set a timeout that will abandon the location loop
};