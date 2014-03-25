<?php 
    
    use RescueMe\User;
    use RescueMe\Properties;
         
    global $positions; 
    
    $user_id = User::currentId();
    
?>

<script>
var markers = new Array();
var circles = new Array();
var infowindows = new Array();
var lastInfoWindow = null;
var markerNo = 0;
var type = '<?=Properties::get(Properties::MAP_DEFAULT_BASE, $user_id)?>';
var posFormat = '<?=Properties::text(Properties::MAP_DEFAULT_FORMAT, $user_id)?>';
</script>
<script src="<?=APP_URI?>js/map.js"></script>
<script>   
    function initialize() {
        
        <?
        foreach ($positions as $key=>$value) {
            if ($value->acc < 1000) {
                $centerMap = $value;
                break;
            }
        }
        if(!isset($centerMap)) {
            $centerMap = reset($positions);
        }

        if($centerMap !== false) { ?>

        R.map.load('map', <?=$centerMap->lat;?>, <?=$centerMap->lon;?>, <?=$centerMap->acc;?>, type);

     <? } else { ?>

            if (navigator.geolocation)
            {
                navigator.geolocation.getCurrentPosition(function(position) {
                    R.map.load('map', position.coords.latitude, position.coords.longitude, position.coords.accuracy, type);
                });
            }

            // TODO: Add default location to properties/configuration, use Oslo for now.
            R.map.load('map', 10.75225, 59.91387, 30000, type);
                 
     <? } ?>
    }
    
    function bindMarker(marker, map, infowindow) {
        google.maps.event.addListener(marker, 'click', function() { 
            showInfoWindow(marker, map, infowindow)
	}); 
    }
    
    function bindLiElement(li, marker, map, infowindow) {
        li.click(function() {
            R.map.panTo(li.attr('data-pan-to'));
            showInfoWindow(marker, map, infowindow);
        });
    }
    
    function showInfoWindow(marker, map, infowindow) {
        if (lastInfoWindow !== null) {
                lastInfoWindow.close();
            }
        lastInfoWindow = infowindow;
        infowindow.open(map,marker);
    }
        
    function addPosition(lat, lon, acc, alt, timeText, posText, posTextClean, timeSince) {
        var color = 'green';
        if (acc > 750)
            color = 'red';
        else if (acc > 400)
            color = 'yellow';
        
        markerNo = markers.length;
                
        var li = $("<li/>", {"class": "position text-left clearfix well well-small", 
                              "id": "position-"+markerNo,
                              "data-pan-to": markerNo});
        
        var span = $("<span/>").html(posTextClean + ' &plusmn; '+acc+' m');
        var time = $("<time/>", {"datatime": timeText}).text(timeSince);

        span.append(time);
        li.append(span);
        
        if(typeof google !== "undefined") {
            
            markerNo = markers.length;
            markers[markerNo] = new google.maps.Marker({
                      map: map,
                      position: new google.maps.LatLng(lat, lon),
                      draggable: false,
                      icon: R.map.getMarkerImage(color),
                      title: '+/- '+acc+' meter ('+timeText+')'
                });
            circles[markerNo] = new google.maps.Circle({
                      strokeColor: color,
                      fillOpacity: 0.1,
                      map: map,
                      radius: acc
                });
            circles[markerNo].bindTo('center', markers[markerNo], 'position');
            infowindows[markerNo] = new google.maps.InfoWindow({
                    content: '<u><?=Properties::text(Properties::MAP_DEFAULT_FORMAT, $user_id)?>:</u><br /> '+
                             posText+'<br /><br />'+
                             '<u>H&oslash;yde:</u> '+alt +' moh<br />'+
                             '<u>N&oslash;yaktighet:</u> ± '+acc+' meter'
                });
            bindMarker(markers[markerNo], map, infowindows[markerNo])
            markers[markerNo].acc = acc;
             
            bindLiElement(li, markers[markerNo], map, infowindows[markerNo]);
            
            R.map.panTo(markers.length-1);
            
         }
        
         if (acc <= 1000) {
            $('#under1km').prepend(li);
         }
         else {
            $('#over1km').prepend(li);    
         }
    }

    $(document).ready(function() {
        if(typeof google !== "undefined") {
            initialize();
        } else {
            $("#map").html('<p class="map"><?=T_("Google Maps not loaded")?></p>');

        }
        setInterval(function(){R.updateTimes()}, 1000);
    });

    function ajaxFetchPosition() {
        R.longFetch('<?=ADMIN_URI."positions/".$_GET['id']?>', R.map.ajaxAddPos, {num: markers.length},
                    180000, ajaxFetchPosition);
     }
     
</script>
