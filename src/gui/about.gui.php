<p class="lead muted"><b><?= TITLE ?></b></p>

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
    Det sendes to SMS til brukeren; SMS 1 inneholder info om sporingslenke, SMS 2 inneholder info om GPS-innstillinger. Delingen sikrer at den savnede
    får den viktigste infoen først, og kan starte sporing fortest mulig. SMS 2 sendes kun hvis første posisjonering er unøyaktig, og kun én gang per 
    savnet.
</p>
<p>
    Posisjoneringssiden den savnede åpner er komprimert maksimalt for å kunne gi en raskest mulig innlasting, uansett internetthastighet. Selv med 
    veldig dårlig internettilkobling skal siden lastes inn i løpet av sekunder.
</p>

<div style="height: 25px;"></div>

<p class="lead">Hvor mange finner vi?</p>
<p>Det er mange grunner til at sporinger ikke fører til lokalisering. Hvis savnede er utenfor dekning,
    tom for batteri, velger å ikke klikke på lenken i SMSen, eller ikke klarer å aktivere deling av
    posisjon med nettleseren vil vi ikke klare å lokalisere telefonen.</p>

<?insert_insights('trace', 'ratios', 90)?>

<div style="height: 25px;"></div>

<p class="lead">Hvem utvikler <?=TITLE?>?</p>
<p><?=TITLE?> utvikles av frivillige i <a href="https://discoos.org">DISCO Open Source</a>, vederlagsfritt for redningstjenesten.</p>

<div style="height: 25px;"></div>

<p class="lead">Kontaktinformasjon</p>
<div class="row">
    <div class="span3">
        <p>Merk at <?=TITLE?> driftes uten noen garantier. Følgende personer kan kontaktes ved behov:</p>
    </div>
    <div class="contact span2 text-center" style="height: 75px;">
        <div class="name">Sven-Ove Bjerkan</div>
        <div class="phone"><a href="tel:+4798846414">+47 98846414</a></div>
        <div class="mail"><a href="mailto:sven-ove@discoos.org">sven-ove@discoos.org</a></div>
    </div>
    <div class="contact span2 text-center" style="height: 75px;">
        <div class="name">Kenneth Gulbrandsøy</div>
        <div class="phone"><a href="tel:+4793258930">+47 93258930</a></div>
        <div class="mail"><a href="mailto:sven-ove@discoos.org">kenneth@discoos.org</a></div>
    </div>
</div>

