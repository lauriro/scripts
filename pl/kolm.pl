
struktuur( [ sober(_,_,_)
           , sober(_,_,_)
		   , sober(_,_,_)
		   ]).

nimi(sober(A,_,_),A).
kodakondsus(sober(_,B,_),B).
sport(sober(_,_,C),C).

oli_parem(A,B,[A,B,_]).
oli_parem(A,C,[A,_,C]).
oli_parem(B,C,[_,B,C]).

first([X|_],X).

vastus:-
	struktuur(Sobrad),
	oli_parem(S11,S12,Sobrad),
	nimi(S11,michael),
	sport(S11,korvpall),
	kodakondsus(S12,ameerika),
	oli_parem(S21,S22,Sobrad),
	nimi(S21,simon),
	kodakondsus(S21,iisraeli),
	sport(S22,tennis),
	first(Sobrad,S3),
	sport(S3,kriket),
	member(S4,Sobrad),
	nimi(S4,Nimi),
	kodakondsus(S4,austraalia),
	member(S5,Sobrad),
	nimi(S5,richard),
	sport(S5,Sport),
	write('Austraallane on '), write(Nimi), nl,
	write('Richard mängib '), write(Sport), nl.


