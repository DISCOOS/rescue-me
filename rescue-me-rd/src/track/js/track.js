  	var base = R.admin.url;
  	var url = base + '#ID-#NUM/';
	var attempts = parseInt(location.href.replace(url, ''));
	attempts = (isNaN(attempts))
		attempts = 1;
	url += (attempts+1);
  
    function getLocation() {
      var x=document.getElementById("feedback");
      if (navigator.geolocation){
        navigator.geolocation.getCurrentPosition(showPosition, showError, {
          enableHighAccuracy: true,
          requireCoords: true,
          timeout:30000,		  // 30 sek
          maximumAge:30000 });    // 30 sek
      }
      else {
        x.innerHTML="Lokalisering st&oslash;ttes ikke av din telefon.";
      }
    }
    function showError(error){
      var x=document.getElementById("feedback");
      switch(error.code) {
        case error.PERMISSION_DENIED:
          x.innerHTML="Du må bekrefte at du gir tillatelse til &aring; vise posisjon."
          break;
        case error.POSITION_UNAVAILABLE:
          x.innerHTML="Posisjon er utilgjengelig."
            break;
        case error.TIMEOUT:
          x.innerHTML="Du må bekrefte at du gir tillatelse til &aring; vise posisjon raskere."
          break;
        case error.UNKNOWN_ERROR:
          x.innerHTML="Ukjent feil."
            break;
      }
    }
    function showPosition(position){
	  var y = position.coords;
  var x=document.getElementById("feedback");
  if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  }
  else{// code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

  if (y.accuracy > 500 && attempts < 10)
    recalc = true;
  else
    recalc = false;

  if (!recalc) {
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
          x.innerHTML=xmlhttp.responseText;
        }
	  }
     }

  var url = base + "s/#ID-#NUM/"+y.latitude+"/"+y.longitude+"/"+y.accuracy+"/"+y.altitude;
  xmlhttp.open("GET",url,true);
  xmlhttp.send();


  if (recalc) {
    x.innerHTML = 'Vi fant posisjonen din med +/- '+Math.floor(y.accuracy)+' meter. <br />'+
      'Vi pr&oslash;ver &aring; finne mer n&oslash;yaktig posisjon om <span id="sek">10</span> sek...';
    setTimeout('window.location.href=url', 10000);
	setTimeout('countdown()', 1000);
  }
}

function countdown() {
	x = document.getElementById("sek");
	x.innerHTML = (parseInt(x.innerHTML)-1);
	setTimeout('countdown()', 1000);
}
