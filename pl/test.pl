:- encoding(utf8).
:- use_module(library(random)).
:- [vastused, 'antonym.pl', 'synonym.pl'].

synonyms(A, B):-
    synonym(List)
  , member(A, List) -> member(B, List) ; assertz( synonym([A]) ), B = A.

antonyms(A, B):-
    synonyms(A, A1)
  , ( antonym(A1, B1); antonym(B1, A1) )
  , synonyms(B1, B).

antonym_add(A, B):-
    not(antonyms(A, B))
  , synonyms(A, A1), !
  , synonyms(B, B1), !
  , assertz( antonym(A1, B1) )
  , update_file( 'antonym' ).

synonym_add(A, B):-
    not(synonyms(A, B))
  , synonym(List)
  , member(A, List), !
  , retract( synonym(List) )
  , assertz( synonym([List|B]) ).

vasta_random(Teema):-
    vastused(Teema, Vastused)
  , length(Vastused, Len)
  , random(0, Len, Rnd)
  , nth0(Rnd, Vastused, Vastus)
  , write(Vastus), tab(1).

õpi(Jutt):-
	vasta_random(selge), vasta_random(õpeta), nl.


vasta(Jutt):-
	vasta_random(küsi), nl.

update_file(Pred):-
    string_concat(Pred, '.pl', FileName)
  , telling(Old)
  , tell(FileName)
  , listing( Pred )
  , told
  , tell(Old).

reload:-
    ['antonym.pl', 'synonym.pl'].
    
saveall:-
    update_file( 'antonym' )
  , update_file( 'synonym' ).