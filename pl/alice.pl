
% R. Smullyan
% T.Tamme 2000 (SWI-Prolog)

l�vivaletab(esmasp�ev).
l�vivaletab(teisip�ev).
l�vivaletab(kolmap�ev).
�kssarvvaletab(neljap�ev).
�kssarvvaletab(reede).
�kssarvvaletab(laup�ev).

l�vit�tt(X):- \+l�vivaletab(X).
�kssarvt�tt(X):- \+�kssarvvaletab(X).

eile(esmasp�ev,p�hap�ev).
eile(teisip�ev,esmasp�ev).
eile(kolmap�ev,teisip�ev).
eile(neljap�ev,kolmap�ev).
eile(reede,neljap�ev).
eile(laup�ev,reede).
eile(p�hap�ev,laup�ev).

l�vi(X):-eile(X,Y),l�vit�tt(X),l�vivaletab(Y).
l�vi(X):-eile(X,Y),l�vivaletab(X),l�vit�tt(Y).
�kssarv(X):-eile(X,Y),�kssarvt�tt(X),�kssarvvaletab(Y).
�kssarv(X):-eile(X,Y),�kssarvvaletab(X),�kssarvt�tt(Y).

solve(X):-
    l�vi(X),
    �kssarv(X).

?-solve(X), write(X), nl.

