  	var vb = 'http://savnet.ntrkh.no/';
  	var vu = vb + '#ID-#NUM/';
	var vf = parseInt(location.href.replace(vu, ''));
	if (isNaN(vf))
		vf = 1;
	vu += (vf+1);
  
    function gL() {
      var x=document.getElementById("f");
      if (navigator.geolocation){
        navigator.geolocation.getCurrentPosition(sP, sE, {
          enableHighAccuracy: true,
          requireCoords: true,
          timeout:30000,		  // 30 sek
          maximumAge:30000 });    // 30 sek
      }
      else {
        x.innerHTML="Lokalisering st&oslash;ttes ikke av din telefon.";
      }
    }
    function sE(err){
      var x=document.getElementById("f");
      switch(err.code) {
        case err.PERMISSION_DENIED:
          x.innerHTML="Du må bekrefte at du gir tillatelse til &aring; vise posisjon."
          break;
        case err.POSITION_UNAVAILABLE:
          x.innerHTML="Posisjon er utilgjengelig."
            break;
        case err.TIMEOUT:
          x.innerHTML="Du må bekrefte at du gir tillatelse til &aring; vise posisjon raskere."
          break;
        case err.UNKNOWN_ERROR:
          x.innerHTML="Ukjent feil."
            break;
      }
    }
    function sP(position){
	  var y = position.coords;
  var x=document.getElementById("f");
  if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  }
  else{// code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

  if (y.accuracy > 500 && vf < 10)
    recalc = true;
  else
    recalc = false;

  if (!recalc) {
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          x.innerHTML=xmlhttp.responseText;
        }
	  }
     }

  var vu = vb+ "s/#ID-#NUM/"+y.latitude+"/"+y.longitude+"/"+y.accuracy+"/"+y.altitude;
  xmlhttp.open("GET",vu,true);
  xmlhttp.send();


  if (recalc) {
    x.innerHTML = 'Vi fant posisjonen din med +/- '+Math.floor(y.accuracy)+' meter. <br />'+
      'Vi pr&oslash;ver &aring; finne mer n&oslash;yaktig posisjon om <span id="sek">10</span> sek...';
    setTimeout('window.location.href=vu', 10000);
	setTimeout('cD()', 1000);
  }
}

function cD() {
	x = document.getElementById("sek");
	x.innerHTML = (parseInt(x.innerHTML)-1);
	setTimeout('cD()', 1000);
}
