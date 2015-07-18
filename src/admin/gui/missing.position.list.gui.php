<?php 
    
    use RescueMe\Domain\User;
    use RescueMe\Properties;
         
    global $positions; 
    
    $user_id = User::currentId();
    
?>

<script>
var markers = [];
var circles = [];
var infowindows = [];
var lastInfoWindow = null;
var markerNo = 0;
var type = '<?=Properties::get(Properties::MAP_DEFAULT_BASE, $user_id)?>';
var posFormat = '<?=Properties::text(Properties::MAP_DEFAULT_FORMAT, $user_id)?>';
</script>
<script src="<?=APP_URI?>js/map.js"></script>
<script>   
    function initialize() {
        <?php
        foreach (array_reverse($positions) as $key=>$value) {
            if ($value->acc < Properties::get(Properties::LOCATION_DESIRED_ACC, $user_id)*0) {
                $centerMap = $value;
                break;
            }
        }
        if(!isset($centerMap)) {
            $centerMap = end($positions);
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

        $tz = date_default_timezone_get();

        $params = Properties::getAll($user_id);

        ksort($positions);
        
        foreach ($positions as $key=>$value) {
            $posText = str_replace("'", "\\'", format_pos($value, $params));
            $posTextClean = str_replace("'", "\\'", format_pos($value, $params, false));
            echo "R.map.addPosition($value->lat, $value->lon, $value->acc, $value->alt,".
                    "'".$posText."', '$posTextClean','".format_tz($value->timestamp)."');";
            $i++;
        }
        echo 'ajaxFetchPosition();';
    ?>
    }
                   
    $(document).ready(function() {
        if(typeof google !== "undefined") {
            initialize();
        } else {
            $("#map").html('<p class="map"><?=_("Google Maps not loaded")?></p>');
        }
        setInterval(function(){R.updateTimes()}, 1000);
    });

    function ajaxFetchPosition() {
        R.longFetch('<?=ADMIN_URI."positions/".$_GET['id']?>', R.map.ajaxAddPos, {num: markers.length},
                    180000, ajaxFetchPosition);
     }
     
</script>