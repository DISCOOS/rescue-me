<?php global $positions; ?>

<script src="<?=APP_URI?>js/map.js"></script>
<script>
    var markers = {};
    var lastInfoWindow = {};
    
    type = '<?=RescueMe\Properties::get('map.default.base', RescueMe\User::currentId())?>';
    
    function initialize() {
        <?php
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
            
     <? } 

        $i = 0;
        foreach ($positions as $key=>$value) {
            $dms['lat'] = dec_to_dms($value->lat);
            $dms['lon'] = dec_to_dms($value->lon);
                $gPoint = new gPoint;
            $gPoint->setLongLat($value->lon, $value->lat);
            $gPoint->convertLLtoTM();
            echo "
            var color = 'green';
            if (".$value->acc." > 750)
                color = 'red';
            else if (".$value->acc." > 400)
                color = 'yellow';

            var marker_".$i." = new google.maps.Marker({
                  map: map,
                  position: new google.maps.LatLng(".$value->lat.", ".$value->lon."),
                  draggable: false,
                  icon: R.map.getMarkerImage(color),
                  title: '+/- ".$value->acc." meter (DTG ". format_dtg($value->timestamp) .")'
            });
            var circle_".$i." = new google.maps.Circle({
                  strokeColor: color,
                  fillOpacity: 0.1,
                  map: map,
                  radius: ".$value->acc."
            });
            circle_".$i.".bindTo('center', marker_".$i.", 'position');


            var infowindow_".$i." = new google.maps.InfoWindow({
                content: '<u>Posisjon:</u><br /> '+
                         '".$dms['lat']['deg']."&deg; ".$dms['lat']['min']."\' ".floor($dms['lat']['sec'])."\'\'<br />'+
                         '".$dms['lon']['deg']."&deg; ".$dms['lon']['min']."\' ".floor($dms['lon']['sec'])."\'\'<br /><br />'+
                         '<u>UTM:</u><br /> '+
                         '".$gPoint->getNiceUTM()."<br /><br />'+
                                         '<u>H&oslash;yde:</u> ".$value->alt." moh<br />'+
                         '<u>N&oslash;yaktighet:</u> ".$value->acc." meter'
            });

            google.maps.event.addListener(marker_".$i.", 'click', function() {
                if (lastInfoWindow[0] != null) {
                    lastInfoWindow[0].close();
                }
                lastInfoWindow[0] = infowindow_".$i.";	
                infowindow_".$i.".open(map,marker_".$i.");
            });

            marker_".$i.".acc = ".$value->acc.";
            markers[".$i."] = marker_".$i.";";
            $i++;
        }
    ?>
            
    }

    $(document).ready(function() {
        if(typeof google !== "undefined") {
            initialize();
        } else {
            $("#map").html('<p class="map"><?=_("Google Maps not loaded")?></p>');
        }      
    });
    
</script>
