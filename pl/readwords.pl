% Kirjavahemärgid
punctuation(46). /*.*/
punctuation(X):-[X]="?". /*'?' kood on 63*/

% Erisümbolid
not_other(_):-at_end_of_stream, !. /*-1, eof*/
not_other(10):-!. /*lf*/
not_other(32):-!. /*tühik*/
not_other(C):-punctuation(C), !.

read_words(_):- at_end_of_stream, !, fail.
read_words(Words):-
  get0(C),
  read_words(C,Words).

read_words(10,[]):-!. /*lf*/
read_words(32,Words):-!, read_words(Words). /*tühik*/
read_words(C,[Word|Words]):-
  punctuation(C), !,
  name(Word,[C]), read_words(Words).
read_words(C,[Word|Words]):- /*sõna algus*/
  read_rest_of_word(Chars,LeftOver),
  name(Word,[C|Chars]),
  read_words(LeftOver,Words).

read_rest_of_word(Chars,LeftOver):-
  get0(C),
  read_rest_of_word(C,Chars,LeftOver).

read_rest_of_word(C,[],C):-
  not_other(C), !.
read_rest_of_word(C,[C|Chars],LeftOver):-
  read_rest_of_word(Chars,LeftOver).
