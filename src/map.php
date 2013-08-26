<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id']))
	die('Ugyldig link!');

require_once('common.inc.php');

$missing = new \RescueMe\Missing();
$missing->getMissing($_GET['id']);
$positions = $missing->getPositions();

function dec_to_dms($dec) {
	// Converts decimal longitude / latitude to DMS
	// ( Degrees / minutes / seconds ) 
	
	// This is the piece of code which may appear to 
	// be inefficient, but to avoid issues with floating
	// point math we extract the integer part and the float
	// part by using a string function.

    $vars = explode(".",$dec);
    $deg = $vars[0];
    $tempma = "0.".$vars[1];

    $tempma = $tempma * 3600;
    $min = floor($tempma / 60);
    $sec = $tempma - ($min*60);

    return array("deg"=>$deg,"min"=>$min,"sec"=>$sec);
}    

require_once('gPoint.class.php');
$gPoint = new gPoint();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title><?= MISSING_PERSON ?> - <?=$missing->m_name;?></title>
<script src="http://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_API_KEY ?>&sensor=false">
</script>

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

	?>
	var mapCenter = new google.maps.LatLng(<?php echo $centerMap->lat;?>, <?php echo $centerMap->lon;?>);
	var zoom = getZoom(<?=$centerMap->acc;?>);

	var mapProp = {
	  'center': mapCenter,
	  'zoom': zoom,
	  'minZoom': 7,
	  'mapTypeControl':false,
	  'streetViewControl': false,
	  'mapTypeId':'topo2'
	};
	map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
	map.mapTypes.set('topo2',new StatkartMapType("Kart", "topo2"));

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
				 '<u>UTM (".$gPoint->Z()."):</u><br /> '+
				 '".floor($gPoint->N())."<br />".floor($gPoint->E())."<br /><br />'+
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
	if (acc < 200)
		return 16;
	else if (acc < 750)
		return 15;
	else if (acc < 1200)
		return 14;
	else if (acc < 1500)
		return 13;
	else
		return 11;
}

google.maps.event.addDomListener(window, 'load', initialize);
</script>
</head>

<body>
<h1><?=$missing->m_name;?> (Mob: <?=$missing->m_mobile;?>)</h1>

<div id="googleMap" style="float: left; width:800px;height:500px;"></div>
<div id="sidebar" style="float:left; margin-left: 10px">
<b><u>Posisjoner:</u></b><br />
<?php
$i = 0;
foreach ($positions as $key=>$value) {
	if ($value->acc < 1000)
		echo '<a href="#" onClick="panMapTo('.$i.')">'.($i+1).'. N&oslash;yaktighet '.$value->acc.' meter (DTG: '.format_dtg($value->timestamp,true).')</a><br />';
	$i++;
}
echo '<br /><i>';
$i = 0;
foreach ($positions as $key=>$value) {
	if ($value->acc >= 1000)
		echo '<a href="#" onClick="panMapTo('.$i.')">'.($i+1).'. N&oslash;yaktighet '.$value->acc.' meter (DTG: '.format_dtg($value->timestamp,true).')</a><br />';
	$i++;
}
echo '</i><br /><br /><u>Link som den savnede bruker:</u><br />'.
	APP_URI.'find.php?id='.$_GET['id'].'&num='.$missing->m_mobile;
?>

</div>
<br clear="all" />
<br />
<a href="form.php">Legg til ny savnet eller se andre savnede</a>

</body>
</html>