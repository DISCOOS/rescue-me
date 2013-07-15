<?php
global $positions;
?>
<script>
var markers = {};
var lastInfoWindow = {};
function initialize() {
    <?php
	foreach ($positions as $key=>$value) {
		if ($value->acc < 1000) {
			$centerMap = $value;
			break;
		}
	}
	if(!isset($centerMap))
		$centerMap = reset($positions);
    
	?>
	var zoom = getZoom(<?=$centerMap->acc;?>);
    <? if(isset($centerMap)) {?>
	var mapProp = {
      'center': new google.maps.LatLng(<?=$centerMap->lat;?>, <?=$centerMap->lon;?>),
	  'zoom': zoom,
	  'minZoom': 7,
	  'mapTypeControl':false,
	  'streetViewControl': false,
	  'mapTypeId':'topo2'
	};
    <? } else { ?>
    var mapProp = {
	  'zoom': zoom,
	  'minZoom': 7,
	  'mapTypeControl':false,
	  'streetViewControl': false,
	  'mapTypeId':'topo2'
	};
    <? } ?>

	map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
	map.mapTypes.set('topo2',new StatkartMapType("Kart", "topo2"));
        
        google.maps.event.addListener(map, 'click', function() {
		if (lastInfoWindow[0] != null) {
			lastInfoWindow[0].close();
		}
        });

	var icons = new Array();
	icons["red"] = new google.maps.MarkerImage("http://www.google.com/intl/en_us/mapfiles/ms/micons/red-dot.png",
	      // This marker is 32 pixels wide by 32 pixels tall.
	      new google.maps.Size(32, 32),
	      // The origin for this image is 0,0.
	      new google.maps.Point(0,0),
	      // The anchor for this image is at 16,32.
	      new google.maps.Point(16, 32));
	function getMarkerImage(iconColor) {
	   if ((typeof(iconColor)=="undefined") || (iconColor==null)) {
	      iconColor = "red";
	   }
	   if (!icons[iconColor]) {
	      icons[iconColor] = new google.maps.MarkerImage("http://www.google.com/intl/en_us/mapfiles/ms/micons/"+ iconColor +"-dot.png",
	      // This marker is 32 pixels wide by 32 pixels tall.
	      new google.maps.Size(32, 32),
	      // The origin for this image is 0,0.
	      new google.maps.Point(0,0),
	      // The anchor for this image is at 16,32.
	      new google.maps.Point(16, 32));
	   }
	   return icons[iconColor];
}

<?php
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
          icon: getMarkerImage(color),
          title: '+/- ".$value->acc." meter (DTG ". date('d-Hi', $value->timestamp) .")'
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
	markers[".$i."] = marker_".$i;
	$i++;
}
?>

}

function StatkartMapType(name, layer) {
	this.layer = layer;
	this.name = name;
	this.alt = name;
	this.tileSize = new google.maps.Size(256,256);
	this.maxZoom = 18;
	this.getTile = function(coord, zoom, ownerDocument) {
		var div = ownerDocument.createElement('DIV');
		div.style.width = this.tileSize.width + 'px';
		div.style.height = this.tileSize.height + 'px';
		div.style.backgroundImage = "url(http://opencache.statkart.no/gatekeeper/gk/gk.open_gmaps?layers=" +
			this.layer + "&zoom=" + zoom + "&x=" + coord.x + "&y=" + coord.y + ")";
		return div;
	};
}

function panMapTo(markerNo) {
	if (lastInfoWindow[0] != null) {
		lastInfoWindow[0].close();
		lastInfoWindow[0] = null;
	}
	map.panTo(markers[markerNo].getPosition());
	map.setZoom(getZoom(markers[markerNo].acc));
}

function getZoom(acc) {
	if (acc> 0 && acc < 200)
		return 16;
	else if (acc> 0 && acc < 750)
		return 15;
	else if (acc> 0 && acc < 1200)
		return 14;
	else if (acc> 0 && acc < 1500)
		return 13;
	else
		return 11;
}

google.maps.event.addDomListener(window, 'load', initialize);
</script>
