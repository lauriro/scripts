:-discontiguous mother/2, married/2, male/1, female/1.

% faktid
married(kalle, leida).
married(valmar,inna).

mother(valmar, leida).
mother(ene,    linda).
mother(inna,   linda).
mother(laivi,  linda).

mother(ingrid, inna).
mother(lauri,  inna).
mother(maili,  inna).

male(kalle).
male(valmar).
male(lauri).

female(leida).
female(ene).
female(inna).
female(laivi).
female(ingrid).
female(maili).

% reeglid

% isa on ema abikaasa.
father(X, Father):-
  mother(X, Mother),
  married(Father, Mother).

% õde on sama ema erinev naissoost laps
sister(X,Sister):-
  mother(X, Mother),
  mother(Sister, Mother),
  female(Sister),
  X\=Sister.

% vend on sama ema erinev meessoost laps
brother(X,Brother):-
  mother(X, Mother),
  mother(Brother, Mother),
  male(Brother),
  X\=Brother.

% tädi on ema õde.
aunt(X,Aunt):-
  mother(X, Mother),
  sister(Mother, Aunt).

% onu on ema vend.
uncle(X,Uncle):-
  mother(X, Mother),
  brother(Mother, Uncle).

% vanaema on ema ema
grandmother(X, Grandmother):-
  mother(X, Mother),
  mother(Mother, Grandmother).

% vanaema on isa ema
grandmother(X, Grandmother):-
  father(X, Father),
  mother(Father, Grandmother).

% vanaisa on vanaema abikaasa
grandfather(X, Grandfather):-
  grandmother(X, Grandmother),
  married(Grandfather, Grandmother).

