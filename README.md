# Tiešsaistes spēļu cenu salīdzināšanas un vēstures uzskaites sistēma

## Projekta apraksts
Šī projekta mērķis ir izstrādāt tiešsaistes platformu, kas lietotājiem ļauj vienuviet
apskatīt, analizēt un salīdzināt datorspēļu cenas vairākos populāros digitālajos veikalos.
Sistēma nodrošina arī cenu vēstures uzskaiti, ļaujot lietotājiem pieņemt finansiāli izdevīgākus
lēmumus, iegādājoties spēles.

Pašlaik spēlētājiem bieži ir manuāli jāapmeklē vairākas vietnes, lai salīdzinātu cenas un
atrastu atlaides. Izstrādājamā sistēma automatizē šo procesu, apkopojot datus no dažādiem
avotiem un attēlojot tos lietotājam pārskatāmā formā.

## Mērķauditorija
- Aktīvi datorspēļu spēlētāji
- Lietotāji, kuri spēles iegādājas neregulāri
- Personas, kas vēlas sekot cenu izmaiņām un atlaidēm

## Galvenās funkcijas
- Spēļu meklēšana pēc nosaukuma, žanra vai veikala
- Cenu salīdzināšana starp vairākiem digitālajiem veikaliem
- Spēļu cenu vēstures attēlošana grafiku veidā
- Vēlmju saraksta izveide un pārvaldība
- Automātiski paziņojumi par cenu samazinājumiem
- Lietotāju reģistrācija, pieteikšanās un profila pārvaldība
- Valūtas un reģiona maiņa
- Administratora iespējas pārvaldīt lietotāju un spēļu datus
- Automātiska datu sinhronizācija ar ārējiem avotiem

## Izmantotie datu avoti
Sistēma iegūst informāciju no publiskiem API:
- Steam
- Epic Games Store
- GG.deals
- IsThereAnyDeal

## Datu bāzes struktūra
Sistēmā tiek glabāta informācija par:
- Lietotājiem
- Spēlēm
- Veikaliem
- Cenu vēsturi
- Vēlmju sarakstiem
- Paziņojumiem
- Administratora datiem

Datu modelēšanai tiek izmantots entītiju–relāciju (ER) modelis pēc Pitera Čena metodoloģijas,
kas nodrošina strukturētu un loģisku datu organizāciju.

## Projekta ieguvumi
- Samazina laiku, kas nepieciešams cenu meklēšanai
- Nodrošina pārskatāmu cenu dinamiku
- Palīdz pieņemt ekonomiski izdevīgākus pirkumu lēmumus
- Apvieno vairākus informācijas avotus vienā platformā
