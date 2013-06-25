  	var base = 'http://savnet.ntrkh.no/';
  	var url = base + '#ID-#NUM/';
	var forsok = parseInt(location.href.replace(url, ''));
	if (isNaN(forsok))
		forsok = 1;
	url += (forsok+1);
	
	function dummy() {}
  
    function getLocation() {
      var x=document.getElementById("f");
      if (navigator.geolocation){
        navigator.geolocation.getAccurateCurrentPosition(showPosition, showError, dummy, {
          maxWait:20000,		  // 20 sek
          desiredAccuracy:200 });    // 200 m
      }
      else {
        x.innerHTML="Lokalisering st&oslash;ttes ikke av din telefon.";
      }
    }
    function showError(error){
      var x=document.getElementById("f");
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
	  var x=document.getElementById("f");
	  if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	  }
	  else{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }


    xmlhttp.onreadystatechange=function() {
	if (xmlhttp.readyState==4 && xmlhttp.status==200) {
	  x.innerHTML=xmlhttp.responseText;
	}
  }

  var url = base+ "s/#ID-#NUM/"+y.latitude+"/"+y.longitude+"/"+y.accuracy+"/"+y.altitude;
  xmlhttp.open("GET",url,true);
  xmlhttp.send();

  x.innerHTML = 'Vi fant posisjonen din med +/- '+Math.floor(y.accuracy)+' meter. <br />'+
      'Vi pr&oslash;ver &aring; finne mer n&oslash;yaktig posisjon...';
}
