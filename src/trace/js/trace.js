/*
 * Define 'namespaces'
 */

R = install;


/*
 * Get trace options
 */
var q = R.trace;


/*
 * Get trace messages
 */
var msg = R.trace.msg;

/**
 * Document reference
 */
var d = document;


/*
 * Define reference
 */
var ngl = navigator.geolocation;


/*
 * Last known position 
 */
var lc = null;

/*
 * Last known position accuracy
 */
var la = Infinity;


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
R.trace.locate = function() {

    /*
     * Countdown timer id
     */
    var cID = 0;

    /*
     * Resend timer id
     */
    var rID = 0;

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

        setTimeout(function() {R.trace.change(rp, se, q);}, q.delay ? 3000 : 0);

        // Register progress indicator
        var im = d.createElement("img");
        im.src = R.app.url+"img/loading.gif";
        i.appendChild(im);
        s.innerHTML = pt(w);
        cID = setTimeout(cd, 1000);

    } else {
        // 'Location not supported on this device'
        f.innerHTML = msg[0];
    }


    /*
     * Show position
     * @param c position coordinates
     * @param a position age
     */
    function sp(c, a) {

        // Notify position found with given accuracy
        var m = msg[1].replace('{0}',Math.ceil(c.accuracy)) + '... <br />';

        // Tell client to check if GPS is off?
        if(a > q.age) m += msg[2] + '<br />';

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
            f.innerHTML += '<br />' + (lc === null ? msg[12] : pm(lc)) + rt();
        }
    }

    /**
     * Insert retry url
     */
    function rt() {
        return '<p><a href>'+msg[13]+'</a></p>';
    }

    /*
     * Show error to client
     */
    function se(e) {
        switch (e.code) {
            case e.PERMISSION_DENIED:
                f.innerHTML = msg[4]+rt();
                break;
            case e.POSITION_UNAVAILABLE:
                f.innerHTML = msg[5];
                break;
            case e.TIMEOUT:
                f.innerHTML = msg[6];
                break;
            case e.UNKNOWN_ERROR:
                f.innerHTML = msg[7];
                break;
        }
    }

    /**
     * Report acquired location to server
     * @param c position coordinates
     * @param t position timestamp
     * @param a accurate position flag
     */
    function rp(c, t, a) {
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

        if(c) {

            var url = R.app.url + "r/" + q.id + "/" + c.latitude + "/" + c.longitude + "/" + c.accuracy + "/" + c.altitude + "/" + t;

            if (xhr !== false) {

                xhr.onreadystatechange = function() {

                    if (xhr.readyState === 4) {

                        if(xhr.status === 200) {
                            // Show current position
                            sp(c, Date.now() - t);
                            // Append response text
                            f.innerHTML += '<b>' + xhr.responseText + '</b>';
                            if(a) {
                                f.innerHTML += rt();
                                s.innerHTML = '';
                                i.innerHTML = '';
                                clearTimeout(cID);
                            } else {
                                // Continue listen for position changes
                                f.innerHTML += '<br />' + msg[3];
                            }
                            clearTimeout(rID);
                        } else {
                            rs(c, t, a);
                        }
                    }
                }

                xhr.open("GET", url, true);

                // Detect connection timeouts (limit to residue)
                xhr.timeout = w * 1000;
                xhr.ontimeout = function() {
                    rs(c, t, a);
                };

                xhr.send();

                // Fallback for those not supporting XMLhttp request. Known: WP 7.8
            } else if (c.accuracy < 300) {

                // No error reporting implemented!!
                window.location = url;
            }
        }
    }

    /**
     * Resend position after failure
     * @param c Position coordinates
     * @param t Position Timestamp
     * @param a accurate position flag
     */
    function rs(c, t, a) {
        if(!rID) {
            // Show current position
            sp(c, Date.now() - t);
            // Show connection warning
            f.innerHTML += msg[10];
            rID = setTimeout(function() {
                rID = 0;
                rp(c, t, a);
            }, 5000);
        }
    }

    /**
     * Print position coordinates
     * @param c position coordinates
     */
    function ps(c) {
        var l = lp(c);
        return msg[8].replace('{0}',l);
    }


    /**
     * Print SMS link
     */
    function pm(c) {
        var l = lp(c);
        var u = q.id + '|' + q.phone + '|' + l + '|' + q.name;
        var ua = navigator.userAgent.toLowerCase();
        var d = (ua.indexOf("iphone") > -1 || ua.indexOf("ipad") > -1) ? ';' : '?';
        return msg[11]+' <a href="sms:'+q.to+d+'body='+u+'">SMS</a>';
    }

    /**
     * Get formatted location
     * @param c
     * @returns {string}
     */
    function lp(c) {
        return (c ? c.longitude.toFixed(4) + 'E ' + c.latitude.toFixed(4) + 'N' : '?');
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
 * @param o Geolocation Options: {
 *                      wait: maximum time to wait for position,
 *                      age: only accept positions younger than this,
 *                      acc: continue to wait for position until desired position is aquired
 *                  }
 * @returns void
 */
R.trace.change = function (gf, ge, o) {

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
        // location even when maximumAge is set to zero!
        if((q <= o.acc) && a <= o.age) {
            gf(p.coords, p.timestamp, true);
            clearTimeout(tID);
            ngl.clearWatch(wID);
            // If the position has improved, report it
        } else if (q < la) {
            gf(p.coords, p.timestamp, false);
            la = q;
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
     * @param c Position coordinates
     */
    var fp = function (c) {
        gf(c, true);
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

    f.innerHTML = msg[9];

    var tID = setTimeout(st, o.wait); // Set a timeout that will abandon the location loop
};
