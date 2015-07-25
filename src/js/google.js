// Prepare (R.map is required)
R.map.icons = [];
R.map.markers = [];
R.map.circles = [];
R.map.infowindows = [];
R.map.lastInfoWindow = null;
R.map.markerNo = 0;

if (typeof google !== "undefined") {

    R.map.icons["red"] = new google.maps.MarkerImage(
        "http://www.google.com/intl/en_us/mapfiles/ms/micons/red-dot.png",
        // This marker is 32 pixels wide by 32 pixels tall.
        new google.maps.Size(32, 32),
        // The origin for this image is 0,0.
        new google.maps.Point(0, 0),
        // The anchor for this image is at 16,32.
        new google.maps.Point(16, 32)
    );

    /**
     * Initialize Google Map into given div
     *
     * @param center {lat,lon,acc}
     */
    R.map.init = function (center) {

        if(!center) {

            R.map.init(R.map.config.default.location);

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    R.map.init(
                        {
                            lat: position.coords.latitude,
                            lon: position.coords.longitude,
                            acc: position.coords.accuracy
                        }
                    );
                });
            }


        } else {

            // Ensure map type
            var type = R.map.config.base || google.maps.MapTypeId.TERRAIN;

            // Prepare map configuration
            var config = {
                center: new google.maps.LatLng(center.lat, center.lon),
                zoom: R.map.getZoom(center.acc),
                minZoom: 7,
                mapTypeId: type,
                panControl: false,
                mapTypeControl: true,
                streetViewControl: false,

                mapTypeControlOptions: {
                    mapTypeIds: [
                        'statkart.topo2',
                        google.maps.MapTypeId.ROADMAP,
                        google.maps.MapTypeId.SATELLITE,
                        google.maps.MapTypeId.HYBRID,
                        google.maps.MapTypeId.TERRAIN
                    ],
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                }

            };

            // Create Google Map
            R.map.google = new google.maps.Map(
                document.getElementById(R.map.config.id),
                config
            );

            // TODO: Register custom layers
            R.map.google.mapTypes.set(
                'statkart.topo2',
                new R.map.StatkartMapType("Norway Topo2", "topo2")
            );

            // Add close infowindow handler
            google.maps.event.addListener(R.map.google, 'click', function () {
                if (R.map.lastInfoWindow !== null) {
                    R.map.lastInfoWindow.close();
                }
            });

            return true;
        }
    };

    R.map.getMarkerImage = function (color) {
        if ((typeof(color) === "undefined") || (color === null)) {
            color = "red";
        }
        if (!R.map.icons[color]) {
            R.map.icons[color] = new google.maps.MarkerImage(
                "http://www.google.com/intl/en_us/mapfiles/ms/micons/" + color + "-dot.png",
                // This marker is 32 pixels wide by 32 pixels tall.
                new google.maps.Size(32, 32),
                // The origin for this image is 0,0.
                new google.maps.Point(0, 0),
                // The anchor for this image is at 16,32.
                new google.maps.Point(16, 32));
        }
        return R.map.icons[color];
    };

    R.map.StatkartMapType = function (name, layer) {
        this.layer = layer;
        this.name = name;
        this.alt = name;
        this.tileSize = new google.maps.Size(256, 256);
        this.maxZoom = 18;
        this.getTile = function (coord, zoom, ownerDocument) {
            var div = ownerDocument.createElement('DIV');
            div.style.width = this.tileSize.width + 'px';
            div.style.height = this.tileSize.height + 'px';
            div.style.backgroundImage = "url(http://opencache.statkart.no/gatekeeper/gk/gk.open_gmaps?layers=" +
                this.layer + "&zoom=" + zoom + "&x=" + coord.x + "&y=" + coord.y + ")";
            return div;
        };
    };

    R.map.panTo = function (markerNo) {
        if (R.map.lastInfoWindow !== null) {
            R.map.lastInfoWindow.close();
            R.map.lastInfoWindow = null;
        }
        R.map.google.panTo(R.map.markers[markerNo].getPosition());
        R.map.google.setZoom(R.map.getZoom(R.map.markers[markerNo].acc));
    };


    /**
     * Get number of positions
     * @returns {number}
     */
    R.map.getPositionCount = function() {
        return R.map.markers.length;
    };


    /**
     * Add position
     * @param p
     *
     * lat, lon, acc, alt, posText, posTextClean, timestamp
     */
    R.map.addPosition = function (p) {
        var color = 'green';
        if (p.acc > 750)
            color = 'red';
        else if (p.acc > 400)
            color = 'yellow';

        // Set current marker
        R.map.markerNo = R.map.markers.length;

        // Create position marker for this position
        var marker = new google.maps.Marker({
            map: R.map.google,
            position: new google.maps.LatLng(p.lat, p.lon),
            draggable: false,
            icon: R.map.getMarkerImage(color),
            title: '± ' + p.acc + ' meter (' + R.format_dtg(p.timestamp) + ')'
        });
        marker.acc = parseInt(p.acc);
        R.map.markers[R.map.markerNo] = marker;

        // Create accuracy circle for this position
        R.map.circles[R.map.markerNo] = new google.maps.Circle({
            map: R.map.google,
            fillOpacity: 0.1,
            strokeColor: color,
            radius: marker.acc
        });
        R.map.circles[R.map.markerNo].bindTo('center', marker, 'position');

        // Create infowindow for this position
        R.map.infowindows[R.map.markerNo] = new google.maps.InfoWindow({
            content: R.map.getPositionInfo(p, R.map.config.format)
        });
        R.map.bindMarker(R.map.markerNo);
        R.map.addToList(p, R.map.markerNo);
    };

    R.map.ajaxAddPos = function (data) {
        try {
            var p = $.parseJSON(data.html.trim());
            R.map.addPosition(p);
            R.map.panTo(R.map.markers.length - 1);
        } catch (err) {
            // TODO: Handle multiple new positions!
            // Currently, it doesn't handle more than one position per request
            // If there are more, a SyntaxError is triggered - workaround with reload
            location.reload();
        }
    };

    R.map.bindMarker = function (markerNo) {
        google.maps.event.addListener(R.map.markers[markerNo], 'click', function () {
            R.map.showInfoWindow(markerNo);
        });
    };

    R.map.bindPosition = function (li, markerNo) {
        li.click(function () {
            R.map.panTo(li.attr('data-pan-to'));
            R.map.showInfoWindow(markerNo);
        });
    };

    R.map.showInfoWindow = function (markerNo) {
        if (R.map.lastInfoWindow !== null) {
            R.map.lastInfoWindow.close();
        }
        R.map.lastInfoWindow = R.map.infowindows[markerNo];
        R.map.lastInfoWindow.open(
            R.map.google,
            R.map.markers[markerNo]
        );
    };

} else {

    R.map.init = function() {
        return false;
    };
}
