korrutis:-
  write('Sisesta X'), write(...),
  read(X).

arvud(0).
arvud(N):-
  N>0,
  write(N), tab(1),
  N1 is N-1,
  arvud(N1).

parvud(Y,Y).
parvud(X,Y):-
  X<Y,
  write(X), tab(1),
  X1 is X+1,
  parvud(X1,Y).


