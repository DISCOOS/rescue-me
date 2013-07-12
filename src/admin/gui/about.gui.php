<h3 class="muted"><?= TITLE ?></h3>
<div class="lead">Lokaliserer personer via deres mobiltelefon</div>
<p>
    <?= TITLE ?> sender en lenke til den savnede via SMS. Med brukerens samtykke (klikk pÃ¥ lenken) blir brukeren lokalisert ved hjelp av 
    <a href="http://www.w3schools.com/html/html5_geolocation.asp">HTML5 Geolocation</a>. Dette krever at telefonen har en nettleser, at telefonen
    er pÃ¥ Internett (datakobling er pÃ¥) og at GPS-posisjonering er aktivert. 
</p>    
<p>
    <strong>Alle posisjoner som mottas fra savnede logges i systemet, ogsÃ¥ de med stor unÃ¸yaktighet</strong>.
</p>
<p>
    Det sendes 2 SMS til brukeren; SMS 1 inneholder info om sporingslenke, SMS 2 inneholder info om GPS-innstillinger. Delingen sikrer at den savnede 
    fÃ¥r den viktigste infoen fÃ¸rst, og kan starte sporing fortest mulig. SMS 2 sendes kun hvis fÃ¸rste posisjonering er unÃ¸yaktig, og kun Ã©n gang per 
    savnet.
</p>
<p>
    Posisjoneringssiden den savnede Ã¥pner er komprimert maksimalt for Ã¥ kunne gi en raskest mulig innlasting, uansett internetthastighet. Selv med 
    veldig dÃ¥rlig internettilkobling skal siden lastes inn i lÃ¸pet av sekunder.
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
	<div class="name">Kenneth GulbrandsÃ¸y</div>
	<div class="phone">932 58 930</div>
	<div class="mail">kenneth@discoos.org</div>
</div>