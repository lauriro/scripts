% Lauri Rooden DK21

:- encoding(utf8).

struktuur( [ maja(_,_,_,_,_)
               , maja(_,_,_,_,_)
               , maja(_,_,_,_,_)
               , maja(_,_,_,_,_)
               , maja(_,_,_,_,_)
           ] ).



värv( maja(A,_,_,_,_), A).
rahvus( maja(_,B,_,_,_), B).
jook( maja(_,_,C,_,_), C).
suits( maja(_,_,_,D,_), D).
loom( maja(_,_,_,_,E), E).


esimene(A,[A|_]).

vasakul(A,B,[A,B|_]).
vasakul(A,B,[_|Ys]):-
    vasakul(A,B,Ys).

naaber(A,B,X):-
    vasakul(A,B,X); vasakul(B,A,X).

keskmine(A,[_,_,A,_,_]).



%1. Britt elab punases majas.
fakt1(X):-
    member(M,X),
    rahvus(M,britt),
    värv(M,punane).

%2. Rootslasel on lemmikloomaks koer.
fakt2(X):-
    member(M,X),
    rahvus(M,rootslane),
    loom(M,koer).

%3. Taanlane joob teed.
fakt3(X):-
    member(M,X),
    rahvus(M,taanlane),
    jook(M,tee).

%4. Roheline maja asub valgest majast vasakul.
fakt4(X):-
    vasakul(V,P,X),
    värv(V,roheline),
    värv(P,valge).

%5. Rohelise maja omanik joob kohvi.
fakt5(X):-
    member(M,X),
    värv(M,roheline),
    jook(M,kohvi).

%6. Inimene, kes suitsetab Pall Malli, kasvatab linde.
fakt6(X):-
    member(M,X),
    suits(M,pallmall),
    loom(M,linnud).

%7. Kollase maja omanik suitsetab Dunhilli.
fakt7(X):-
    member(M,X),
    värv(M,kollane),
    suits(M,dunhilli).

%8. Inimene, kes elab keskmises majas, joob piima.
fakt8(X):-
    keskmine(M,X),
    jook(M,piim).

%9. Norrakas elab esimeses majas.
fakt9(X):-
    esimene(M,X),
    rahvus(M,norrakas).

%10.  Inimene, kes suitsetab Blendi, elab kõrvuti (on naaber) inimesega, kellel on lemmikloomaks kass.
fakt10(X):-
    naaber(A,B,X),
    suits(A,blend),
    loom(B,kass).

%11.  Inimene, kellel on hobune, elab kõrvuti (on naaber) inimesega, kes suitsetab Dunhilli.
fakt11(X):-
    naaber(A,B,X),
    suits(A,dunhilli),
    loom(B,hobune).

%12.  Inimene, kes suitsetab Bluemastersi, joob õlut.
fakt12(X):-
    member(M,X),
    suits(M,bluemastersi),
    jook(M,õlu).

%13.  Sakslane suitsetab Prince'i.
fakt13(X):-
    member(M,X),
    rahvus(M,sakslane),
    suits(M,prince).

%14.  Norrakas elab sinise maja kõrval (tema majast järgmine maja on sinine).
fakt14(X):-
    vasakul(V,P,X),
    rahvus(V,norrakas),
    värv(P,sinine).

%15.  Inimesel, kes suitsetab Blendi, on naabriks see, kes joob vett.
fakt15(X):-
    naaber(A,B,X),
    suits(A,blend),
    jook(B,vesi).


lahenda:-
    struktuur(Majad),
    fakt1(Majad),
    fakt2(Majad),
    fakt3(Majad),
    fakt4(Majad),
    fakt5(Majad),
    fakt6(Majad),
    fakt7(Majad),
    fakt8(Majad),
    fakt9(Majad),
    fakt10(Majad),
    fakt11(Majad),
    fakt12(Majad),
    fakt13(Majad),
    fakt14(Majad),
    fakt15(Majad),
    member(M,Majad),
    loom(M,kalad),
    write('Kalasid omab '), rahvus(M,X), write(X).
