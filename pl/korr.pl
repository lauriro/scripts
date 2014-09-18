% Lauri Rooden DK21

% Abiline stringi kordamiseks
repeat(Str, 1, Str).
repeat(Str, Num, Res):-
	Num1 is Num-1,
	repeat(Str, Num1, Res1),
	string_concat(Str, Res1, Res).

% Abiline stringi täitmiseks tühikutega
pad(Num, Max):-
	pad(Num, Max, 1).
pad(Num, Max, Extra):-
	string_length(Max, MaxLen),
	string_length(Num, NumLen),
	TabLen is MaxLen - NumLen + Extra,
	tab(TabLen), write(Num).


tabeli_rida(_, Max, Max):- nl.
tabeli_rida(Rida, Pos, Max):-
	Pos<Max,
	Num is Rida * Pos,
	pad(Num, Max, 2),
	Next is Pos + 1,
	tabeli_rida(Rida, Next, Max).

tabel(Max):-
	string_length(Max, MaxLen),
	LineLen is ( Max + 1) * ( MaxLen + 2 ) + 2,
	pad('x', Max), write(' |'), tabeli_rida(1, 0, Max),
	repeat('-', LineLen, Line),
	write(Line), nl,
	tabel(0, Max).

tabel(Max,Max).
tabel(Rida, Max):-
	Rida<Max,
	pad(Rida, Max), write(' |'), tabeli_rida(Rida, 0, Max),
	Next is Rida+1,
	tabel(Next, Max).
