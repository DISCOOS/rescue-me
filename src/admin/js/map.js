R = R || {};
R.map = {};
R.map.icons = new Array();

if(typeof google !== "undefined") {
    
    R.map.icons["red"] = new google.maps.MarkerImage(
            "http://www.google.com/intl/en_us/mapfiles/ms/micons/red-dot.png",
            // This marker is 32 pixels wide by 32 pixels tall.
            new google.maps.Size(32, 32),
            // The origin for this image is 0,0.
            new google.maps.Point(0, 0),
            // The anchor for this image is at 16,32.
            new google.maps.Point(16, 32));

    /**
     * Load Google Map into given div
     * 
     * @param string id Element id
     * @param double lat Latitude
     * @param double lon Longitude
     * @param double acc Accuracy
     * @param string type Base map type
     */    
    R.map.load = function (id, lat, lon, acc, type) {
        type = type || google.maps.MapTypeId.TERRAIN;
        var mapProp = {
            center: new google.maps.LatLng(lat, lon),
            zoom: R.map.getZoom(acc),
            minZoom: 7,
            mapTypeId: type,
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
        map = new google.maps.Map(document.getElementById(id), mapProp);
        map.mapTypes.set('statkart.topo2', new R.map.StatkartMapType("Norway Topo2", "topo2"));

        google.maps.event.addListener(map, 'click', function() {
            if (lastInfoWindow[0] !== null) {
                lastInfoWindow[0].close();
            }
        });

    };

    R.map.getMarkerImage = function (iconColor) {
        if ((typeof(iconColor) === "undefined") || (iconColor === null)) {
            iconColor = "red";
        }
        if (!R.map.icons[iconColor]) {
            R.map.icons[iconColor] = new google.maps.MarkerImage("http://www.google.com/intl/en_us/mapfiles/ms/micons/" + iconColor + "-dot.png",
                    // This marker is 32 pixels wide by 32 pixels tall.
                    new google.maps.Size(32, 32),
                    // The origin for this image is 0,0.
                    new google.maps.Point(0, 0),
                    // The anchor for this image is at 16,32.
                    new google.maps.Point(16, 32));
        }
        return R.map.icons[iconColor];
    };

    R.map.StatkartMapType = function (name, layer) {
        this.layer = layer;
        this.name = name;
        this.alt = name;
        this.tileSize = new google.maps.Size(256, 256);
        this.maxZoom = 18;
        this.getTile = function(coord, zoom, ownerDocument) {
            var div = ownerDocument.createElement('DIV');
            div.style.width = this.tileSize.width + 'px';
            div.style.height = this.tileSize.height + 'px';
            div.style.backgroundImage = "url(http://opencache.statkart.no/gatekeeper/gk/gk.open_gmaps?layers=" +
                    this.layer + "&zoom=" + zoom + "&x=" + coord.x + "&y=" + coord.y + ")";
            return div;
        };
    };

    R.map.panTo = function (markerNo) {
        if (lastInfoWindow[0] !== undefined) {
            lastInfoWindow[0].close();
            lastInfoWindow[0] = undefined;
        }
        map.panTo(markers[markerNo].getPosition());
        map.setZoom(R.map.getZoom(markers[markerNo].acc));
    };

    R.map.getZoom = function (acc) {
        if (acc > 0 && acc < 200)
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
}
