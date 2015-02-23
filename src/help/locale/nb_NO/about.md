---

layout: default

---

<div markdown="1" class="hero-unit">

## Lokalisere personer via smarttelefon

Moderne smarttelefoner kan rapportere nåværende posisjon med høy nøyaktighet ved hjelp av åpne standarder som
[HTML5 Geolocation](http://www.w3schools.com/html/html5_geolocation.asp) og
[Javascript](http://wikipedia.org/wiki/JavaScript). **{{TITLE}}** benytter dette til å lokalisere personer som bærer en
smarttelefon, uten noe behov for forhåndsinntallert programvare på telefonen. Dette gjør {{TITLE}} til et spesielt
nyttig verktøy i søks- og redningsoperasjoner.

</div>

## Viktige egenskaper {.lead}

* Alle posisjoner blir logget, også de grove.
* Hvis første posisjon er grovere enn ønskelig sendes det automatisk en ny SMS med instruksjoner om at GPS må slås på.
* Når en nøyaktig posisjon er mottatt sendes det automatisk en SMS med denne til en valgbar mobiltelefonen.
* Nye posisjoner oppdateres i kartet og legges til listen med rapporterte posisjoner automatisk (siden må ikke lastes på nytt).
* Operatører kan benytte {{TITLE}} på både PC og mobiltelefon.

## Begrensninger {.lead}

<span class="text-error">**Ingen verktøy er perfekt**</span>. {{TITLE}} har reelle begrensninger som operatørene må
gjenkjenne og forstå. Først av alt, **det er ingen magi involvert**. Ingen skjulte, hemmelige eller spesielle
teknologier brukes, bare åpne standarder. Dernest, **smarttelefonen må være slått på**. Det kan virke trivielt, men et
tomt batteri gjør det umulig å lokalisere telefonen med {{TITLE}} . Tomt batteri er ikke umulig for oss å løse. Bare
personen som bærer en smarttelefon kan løse dette. Mer vesentlig, så må **smarttelefonen ha nett- og datadekning**.
Dette er trolig det nest vanskeligste kravet å overvinne. Mye innsats og omtanke har blitt lagt ned i designet av
applikasjonen for å øke påliteligheten til sporingsalgoritmen. {{Title}} er i stand til å arbeide med lavest mulig dekning.
Det er imidlertid umulig å overvinne problemet med dårlig dekning helt. * Ingen nettverk, ingen posisjon. Det er så enkelt*.
Kun **smarttelefoner med GPS-mottaker** er mulig å finne med høy nøyaktighet, og den må være skrudd på. Dersom GPS ikke
er tilgjengelig, eller hvis den er ikke i stand til å gi en nøyaktig posisjon, vil du i beste fall kunne motta en grov
posisjon. Grove posisjoner er basert på informasjon hentet fra nettverk og andre kilder, eller i noen tilfeller telefonens
siste kjente posisjon. Grove posisjoner har begrenset verdi, men er ikke verdiløse. De kan fortsatt brukes til å begrense
et søk til stor grad. Den **siste begrensningen er personvern**. Brukere, myndigheter, standardiseringsorganer og
smarttelefonproduserer bryr seg om personvern. {{Title}} må etterkomme reglene for deling av posisjon, inkludert samtykke.
Hvis en brukeren ikke ønsker å dele sin posisjon, *vil {{title}} overholder dette og avslutte sporingen*.


## Hvordan virker det? {.lead}

Smarttelefoner spores ved hjelp av en **4-trinns prosess**. Først sender {{title}} en SMS til smarttelefonen.
Meldingen ber brukeren om å klikke på en link i meldingen. Hvis brukeren **velger å gjøre det**, åpner smarttelefonen
nettleseren og begynner å laste ned en side nøye designet for rask og sikker lokalisering. Under normale omstendigheter,
bør det ta mye mindre enn ett sekund å laste ned og åpne siden. Vanligvis tar det rundt **0,2 sekunder**
hvis telefonen kun har [EGDGE](http://en.wikipedia.org/wiki/2G) (2G-nettverk). Vi har optimert sporingssiden ved å
balansere mellom rask nedlasting (liten størrelse), gjøre den feiltolerant nok til å håndtere kjente
kompatibilitetsproblemer, og brukervennlig nok til å sikre brukerne reagerer raskt og riktig på situasjoner som krever
handling fra brukerens side (slå på nettverkstilkobling, GPS og dataroaming).