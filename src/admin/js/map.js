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
                    'statkart.topo',
                    google.maps.MapTypeId.ROADMAP,
                    google.maps.MapTypeId.SATELLITE,
                    google.maps.MapTypeId.HYBRID,
                    google.maps.MapTypeId.TERRAIN
                ],
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
            }

        };
        map = new google.maps.Map(document.getElementById(id), mapProp);
        map.mapTypes.set('statkart.topo', new R.map.StatkartMapType("Norway Topo4", "topo4"));

        google.maps.event.addListener(map, 'click', function() {
            if (lastInfoWindow !== null) {
                lastInfoWindow.close();
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
        if (lastInfoWindow !== null) {
            lastInfoWindow.close();
            lastInfoWindow = null;
        }
        map.panTo(markers[markerNo].getPosition());
        map.setZoom(R.map.getZoom(markers[markerNo].acc));
    };

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
    
    R.map.addPosition = function(lat, lon, acc, alt, posText, posTextClean, timestamp) {
        var color = 'green';
        if (acc > 750)
            color = 'red';
        else if (acc > 400)
            color = 'yellow';
        
        markerNo = markers.length;
        
        markers[markerNo] = new google.maps.Marker({
                  map: map,
                  position: new google.maps.LatLng(lat, lon),
                  draggable: false,
                  icon: R.map.getMarkerImage(color),
                  title: '+/- '+acc+' meter ('+R.format_dtg(timestamp)+')'
            });
        circles[markerNo] = new google.maps.Circle({
                  strokeColor: color,
                  fillOpacity: 0.1,
                  map: map,
                  radius: acc
            });
        circles[markerNo].bindTo('center', markers[markerNo], 'position');
        infowindows[markerNo] = new google.maps.InfoWindow({
                content: '<u>'+posFormat+':</u><br /> '+
                         posText+'<br /><br />'+
                         '<u>Tidspunkt:</u> '+timestamp +'<br />'+
                         '<u>H&oslash;yde:</u> '+alt +' moh<br />'+
                         '<u>N&oslash;yaktighet:</u> ± '+acc+' meter'
            });
         R.map.bindMarker(markers[markerNo], map, infowindows[markerNo])
         markers[markerNo].acc = acc;

         var li = $("<li/>", {"class": "position text-left clearfix well well-small", 
                              "id": "position-"+markerNo,
                              "data-pan-to": markerNo});
        
        R.map.bindLiElement(li, markers[markerNo], map, infowindows[markerNo]);
        var span = $("<span/>").html(posTextClean + ' &plusmn; '+acc+' m');
        var time = $("<time/>", {"datetime": timestamp}).text(R.format_since(timestamp));
                
        span.append(time);
        li.append(span);

         if (acc <= 1000) {
            $('#under1km').prepend(li);
            $('#under1kmtitle').show();
         }
         else {
            $('#over1km').prepend(li);
            $('#over1kmtitle').show();
         }
    };
    
    R.map.showInfoWindow = function(marker, map, infowindow) {
        if (lastInfoWindow !== null) {
                lastInfoWindow.close();
            }
        lastInfoWindow = infowindow;
        infowindow.open(map,marker);
    };
    
    R.map.ajaxAddPos = function(data) {
        try {
            var p = $.parseJSON(data.html.trim());
            R.map.addPosition(p.lat, p.lon, parseInt(p.acc), p.alt, p.posText, 
                        p.posTextClean, p.timestamp);
            R.map.panTo(markers.length-1);
        } catch(err) {
            // TODO: Handle multiple new positions!
            // Currently, it doesn't handle more than one position per request
            // If there are more, a SyntaxError is triggered - workaround with reload
            location.reload();
        }
    };
    
    R.map.bindMarker = function(marker, map, infowindow) {
        google.maps.event.addListener(marker, 'click', function() { 
            R.map.showInfoWindow(marker, map, infowindow)
	}); 
    };
    
    R.map.bindLiElement = function(li, marker, map, infowindow) {
        li.click(function() {
            R.map.panTo(li.attr('data-pan-to'));
            R.map.showInfoWindow(marker, map, infowindow);
        });
    };
}
