
:-encoding(utf8).
:-[readwords]. %defineerib read_words/1

% minu näidisdialoog
% * Kas lasteaias on suuri lapsi ka?
%   Sa pabistad vist natuke lasteaeda mineku pärast.
% * Suured poisid võivad mind lüüa.
%   Sa kardad haiget saada.
% * Jah, ja nad ei mängi minuga.
%   Sa tunned end üksildasena, kui nad ei mängi sinuga. 

/*dialoog:-
  write('Kuulan sind:'), nl,
  repeat,
  read_words(Xs),
  vasta(Xs,Ys),
  print_words(Ys), nl,
  fail.*/
dialoog:-
  write('Kuulan sind:'), nl,
  repeat,
  read_words(Xs),
  (Xs=[], write('Kas lõpetame? (j/e) '), 
  read_words(Xs2), Xs2=[j]
  -> !
  ;
  vasta(Xs,Ys), 
  print_words(Ys), nl,
  fail).


print_words(Xs):-write(Xs).

% Testige teksti sisse lugemise tööd.
%?-read_words(X).

% arvuti vastamisvariandid
vasta(X,Y):-
  võtmesõna(V6ti,Lause), 
  member(V6ti,X), !, %lõikeoperaator
  Y=Lause.
vasta(X,Y):-jokker(Y).

jokker([räägi,midagi,veel]).
võtmesõna(lasteaias,[Sa,pabistad,vist,
                     natuke,lasteaeda,
                     mineku,pärast,.]).
                     
