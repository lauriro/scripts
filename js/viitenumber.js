
function viitenumber(nr){
	nr += ""

	for (var kaal = [7,3,1], sum=0, i = nr.length, len = i - 1; i--; ) 
		sum += nr.charAt( len-i ) * kaal[i%3];

	return nr+((10-(sum%10))%10)
}

function v2(nr) {
	var sum = 0
	nr += ""

	for (var pos = nr.length, i = 0; pos--;) {
		sum += nr.charAt(pos) * ( (1<<(3-(i++%3)))-1 );
	}

	return nr + ((10-(sum%10))%10)
}

function test(form){
	form.result.value = String(viitenumber(form.nr.value) )
}

/*
- http://www.pangaliit.ee/eng/Codes/
- http://www.pangaliit.ee/et/arveldused/7-3-1meetod

VIITENUMBRI STANDARDI N�UDED
Viitenumbri maksimaalseks pikkuseks on 20 s�mbolit 
ning minimaalne pikkus 2 s�mbolit. 

Viitenumbri viimane s�mbol on kontrollj�rk (st, et 
viitenumbri koostaja kasutada on vahemik 1..19 ja 
l�ppu lisandub 7-3-1 meetodil arvutatud kontrollj�rk). 

Viitenumbri esituses v�ivad s�mbolid olla parema 
loetavuse tagamiseks neljakaupa grupeeritud, kasutades 
eraldajana t�hikut. Viitenumbri elektroonses 
esituskujus t�hikud puuduvad ja neid ei sisestata. 

Kontrollj�rgu arvutamine toimub 7-3-1 meetodi alusel. 

Kontrollj�rgu arvutamine nn. 7-3-1 meetodil: 

Viitenumbri m�rkidele esimene kuni eelviimane (st 
v�lja arvatud kontrollj�rgu koht) seatakse paremalt 
vasakule kaalud 7,3,1,7,...; 

M�rgid korrutatakse kaaludega ning saadud tulemused 
liidetakse kokku;

Leitakse saadud summale (2) j�rgnev k�mne korrutis 
ning lahutatakse sellest saadud summa (2);

saadud arv (3) on kontrollj�rguks ning viitenumbri 
viimaseks m�rgiks. 

Viitenumbri koostab arve l�hetaja ja selle sisu on vaba. 

&lt;?php
function arvutaViitenumber($nr){
    $nr = (string)$nr;
    $kaal = array(7,3,1);
    $sl = $st = strlen($nr);
    while($nr{--$sl}&gt;='0'){
        $total += $nr{($st-1)-$sl}*$kaal[($sl%3)];
    }
    $kontrollnr = ((ceil(($total/10))*10)-$total);
    return $nr.$kontrollnr;
}
?&gt;

&lt;script&gt;
function arvutaViitenumber(nr){
    nr = String(nr);
    var kaal = [7,3,1], total=0;
    var sl = st = nr.length;
    while(nr.charAt(--sl)){
        total += nr.charAt( (st-1)-sl )*kaal[(sl%3)];
    }
    var kontrollnr = ((Math.ceil((total/10))*10)-total);
    return nr+''+kontrollnr;
}
&lt;/script&gt;

*/
