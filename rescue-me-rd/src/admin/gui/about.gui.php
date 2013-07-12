<h3 class="muted"><?= TITLE ?></h3>
<div class="lead">Lokaliserer personer via deres mobiltelefon</div>
<p>
    <?= TITLE ?> sender en lenke til den savnede via SMS. Med brukerens samtykke (klikk på lenken) blir brukeren lokalisert ved hjelp av 
    <a href="http://www.w3schools.com/html/html5_geolocation.asp">HTML5 Geolocation</a>. Dette krever at telefonen har en nettleser, at telefonen
    er på Internett (datakobling er på) og at GPS-posisjonering er aktivert. 
</p>    
<p>
    <strong>Alle posisjoner som mottas fra savnede logges i systemet, også de med stor unøyaktighet</strong>.
</p>
<p>
    Det sendes 2 SMS til brukeren; SMS 1 inneholder info om sporingslenke, SMS 2 inneholder info om GPS-innstillinger. Delingen sikrer at den savnede 
    får den viktigste infoen først, og kan starte sporing fortest mulig. SMS 2 sendes kun hvis første posisjonering er unøyaktig, og kun én gang per 
    savnet.
</p>
<p>
    Posisjoneringssiden den savnede åpner er komprimert maksimalt for å kunne gi en raskest mulig innlasting, uansett internetthastighet. Selv med 
    veldig dårlig internettilkobling skal siden lastes inn i løpet av sekunder.
</p>

<h3>Systemet er utviklet av</h3>
<div class="contact span3">
	<img src="http://graph.facebook.com/svenove/picture" />
	<div class="name">Sven-Ove Bjerkan</div>
	<div class="phone">988 46 414</div>
	<div class="mail">ikt@ntrkh.no</div>
</div>
<div class="contact span3">
	<img src="http://graph.facebook.com/mariusmandal/picture" />
	<div class="name">Marius Mandal</div>
	<div class="phone">928 37 360</div>
	<div class="mail">mariusmandal@gmail.com</div>
</div>
<div class="contact span3">
	<img src="http://graph.facebook.com/kengulb/picture" />
	<div class="name">Kenneth Gulbrandsøy</div>
	<div class="phone">932 58 930</div>
	<div class="mail">kenneth@discoos.org</div>
</div>