
% R. Smullyan
% T.Tamme 2000 (SWI-Prolog)

lõvivaletab(esmaspäev).
lõvivaletab(teisipäev).
lõvivaletab(kolmapäev).
ükssarvvaletab(neljapäev).
ükssarvvaletab(reede).
ükssarvvaletab(laupäev).

lõvitõtt(X):- \+lõvivaletab(X).
ükssarvtõtt(X):- \+ükssarvvaletab(X).

eile(esmaspäev,pühapäev).
eile(teisipäev,esmaspäev).
eile(kolmapäev,teisipäev).
eile(neljapäev,kolmapäev).
eile(reede,neljapäev).
eile(laupäev,reede).
eile(pühapäev,laupäev).

lõvi(X):-eile(X,Y),lõvitõtt(X),lõvivaletab(Y).
lõvi(X):-eile(X,Y),lõvivaletab(X),lõvitõtt(Y).
ükssarv(X):-eile(X,Y),ükssarvtõtt(X),ükssarvvaletab(Y).
ükssarv(X):-eile(X,Y),ükssarvvaletab(X),ükssarvtõtt(Y).

solve(X):-
    lõvi(X),
    ükssarv(X).

?-solve(X), write(X), nl.

