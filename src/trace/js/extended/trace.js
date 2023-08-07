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

/*
 * Get tip messages
 */
var tip = function(c) {
    return R.trace.tip[c];
};

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
R.trace.locate = ()=> {
    
    /*
     * Countdown timer id
     */
    var cID = 0;

    /*
     * Geolocation timeout id
     */
    var lID = 0;

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

    /*
     * Location permission granted flag
     */
    var granted = false;

    /* ===================================================
     *  Register geolocation callback
     * =================================================== */
    if(ngl) {

        /*
         * Check permissions if possible
         */
        R.trace.check(
            () => {
                granted = true;
                ld();
            },
            ()=>
            {
                granted = false;
                re(14);
                f.innerHTML += rt();
                ld();
            },
            () => {
                /* Check if previously denied */
                re(q.located === true ? 15 : 4);
                spd(q.located === true ? 15 : 4);
            },
            (result) => {
                // Denied is handled by geolocation error handler that calls gp (=>sp)
                if (result.state !== 'denied') {
                    ld();
                }
            },
            ld,
        );

    } else {
        // 'Location not supported on this device'
        re(0, msg[0]);
    }

    function ld() {
        if(lID>0) {
            s.innerHTML = '';
            i.innerHTML = '';
            clearTimeout(lID);
        }

        lID = setTimeout(
            () => {
                R.trace.change(rp, se, sp, q);
            },
            q.delay ? 3000 : 0,
        );

        // Register progress indicator
        var im = d.createElement("img");
        im.src = R.app.url+"img/loading.gif";
        i.appendChild(im);
        s.innerHTML = pt(w);
        cID = setTimeout(cd, 1000);
    }
    
    
    /*
     * Show position
     * @param p position coordinates
     * @param a position age
     */
    function sp(c, a) {

        granted = true;

        // Notify position found with given accuracy
        var m = msg[1].replace('{0}',Math.ceil(c.accuracy)) + '... <br />';
        
        // Tell client to check if GPS is off?
        if(a > q.age) m += msg[2] + '<br />';
        
        // Continue listen for position changes    
        m += msg[3] + '<br />';
            
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
            f.innerHTML = (lc === null ? msg[12] : pm(lc)) + rt();
        }
    }
    
    /**
     * Insert retry url
     */
    function rt() {
        return '<p><a href>'+msg[13]+'</a></p>';
    }

    /**
     * Display permission denied information
     */
    function spd(code) {
        if(tip(code) !== undefined) {
            f.innerHTML += '<p>'+tip(code).map((c) => msg[c]).join('<br>')+'</p>';
        }
        f.innerHTML += rt();
    }

    /**
     * Display error to client and rapport it to operator
     */
    function se(e) {
        switch (e.code) {
            case e.PERMISSION_DENIED:
                re(q.located === true ? 15 : 4);
                spd(q.located === true ? 15 : 4);
                granted = false;
                break;
            case e.POSITION_UNAVAILABLE:
                granted = false;
                re(5);
                break;
            case e.TIMEOUT:
                re(6);
                break;
            default:
                re(7);
                break;
        }
    }

    /**
     * Report error to operator
     * @param c Error code
     */
    function re(c) {
        f.innerHTML = msg[c];
        const url = toUrl(['e',q.id,c,msg[c]]);
        send(
            url,
            () => {},
            () => {},
            () => {
                return true;
            }
        );
    }

    /**
     * Build URL from array of parameters
     * @param {[]} params
     */
    function toUrl(params) {
        return R.app.url + params.join('/')
    }

    /**
     * Report acquired location to server
     * @param c location coordinates
     * @param t location timestamp
     * @param u update page information flag
     */
    function rp(c, t, u) {

        granted = true;

        l.innerHTML = ps(c);

        const url = toUrl(['r',q.id,c.latitude,c.longitude,c.accuracy || 0.0,c.altitude || 0.0,t]);

        send(
            url,
            (xhr) => {
                // Update message with response text?
                if(u) {
                    f.innerHTML = xhr.responseText + rt();
                    s.innerHTML = '';
                    i.innerHTML = '';
                    clearTimeout(cID);
                }
            },
            () => {
                f.innerHTML = msg[10];
            },
            () => {
                return c.accuracy < 300;
            }
        );
    }


    /**
     * Send GET request
     * @param {string} url
     * @param {*} ok On response '200 OK'
     * @param {*} err On error (and timeout)
     * @param {*} fb Return true if fallback should be attempted
     */
    function send(url, ok, err, fb) {
        const xhr = nrq();
        if (xhr !== false) {

            xhr.onreadystatechange = () => {

                if (xhr.readyState === 4) {

                    if(xhr.status === 200) {
                        ok(xhr);
                    } else {
                        err(xhr);
                    }
                }
            }

            xhr.open("GET", encodeURI(url), true);
            xhr.send();

            // Detect connection timeouts
            xhr.timeout = w;
            xhr.ontimeout = err;

            // Fallback for those not supporting XMLhttprequest. Known: WP 7.8
        } else if(fb()){
            // No error reporting implemented!!
            window.location = url;
        }
    }

    /**
     * Create new request object
     * @returns {XMLHttpRequest|any|boolean}
     */
    function nrq() {
        if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
            return new XMLHttpRequest();
        }
        else {// code for IE6, IE5
            try {
                return new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
        }
        return false;
    }


    /**
     * Print position coordinates
     * @param c position coordinates
     */
    function ps(c) {
        var l = c.longitude.toFixed(4) + 'E ' + c.latitude.toFixed(4) + 'N';
        return msg[8].replace('{0}',l);
    }
    
    
    /**
     * Print SMS link
     */
    function pm(c) {
        var l = c.longitude.toFixed(4) + 'E ' + c.latitude.toFixed(4) + 'N';
        var u = q.id + '|' + q.phone + '|' + l + '|' + q.name;
        var ua = navigator.userAgent.toLowerCase();
        var d = (ua.indexOf("iphone") > -1 || ua.indexOf("ipad") > -1) ? ';' : '?';
        return msg[11]+' <a href="sms:'+q.to+d+'body='+u+'">SMS</a>';
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
 * @param ge GeoLocation error occurred
 * @param gp Geolocation progress occurred
 * @param o Geolocation Options: {
 *                      wait: maximum time to wait for position, 
 *                      age: only accept positions younger than this, 
 *                      acc: continue to wait for position until desired position is aquired
 *                  }
 * @returns void
 */
R.trace.change = function (gf, ge, gp, o) {

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
        // If the new position has improved by 10%, report it
        } else if (q < la * 0.9) {
            gf(p.coords, p.timestamp, false);
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
    var st = () => {
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

/**
 * Check trace permissions
 * @param g Called when permission is granted
 * @param p Called when permission is going to be prompted
 * @param d Called when permission is denied
 * @param c Called when permission is changed
 * @param u Called when the permissions API are unavailable
 */
R.trace.check = function (g, p, d, c, u) {
    if(navigator.permissions!==undefined) {
        navigator.permissions.query({name: 'geolocation'}).then(function (result) {
            if (result.state === 'granted') {
                g();
            } else if (result.state === 'prompt') {
                p();
            } else if (result.state === 'denied') {
                d();
            }
            result.onchange = () => c(result);
        });
    } else {
        // Assume
        u();
    }
}