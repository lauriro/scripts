{-
Funktsionaalprogrammeerimise kodu�lesanded 2

�lesanded tuleb lahendada individuaalselt. Lahendused saata
meiliaadressile tarmo@cs.ioc.ee. Lahendused tuleb esitada �hes
hs-failis. P�hifunktsioonid peavad kandma neid nimesid, mis �lesandes
n�utud. Kui kasutate oma defineeritud abifunktsioone, peab nende
funktsionaalsus olema failis kommenteeritud.

Punktisummad eri �lesannete eest on erinevad, kokku komplekti eest
20 p.

T�htaeg on 23.12.

Kuni 2 n�dalat hilinenud kodut�� eest saab 50% nominaalpunktidest.

---

Kirjuta programm, kus kasutaja m�ngib arvuti vastu m�ngu Quarto.

Quarto m�ngus on 16 nuppu, iga�ks erinevad, millest iga�ks on 

- hele v�i tume,
- l�hike v�i pikk,
- �mmargune v�i kandiline,
- t�is v�i ��nes.

Iga nupp realiseerib �he kombinatsiooni neist 4 vastandlikust
omadusest.

M�ngu k�igus v�etakse �hisest varust nuppe ja paigutatakse
neid lauale.

Kumbki m�ngija (kasutaja, arvuti) p��ab saada maksimaalse
arvu t�isridu v�i -veerge.

T�isrida v�i -veerg on laua rida v�i veerg (diagonaalid ei
loe), kus on neli mingi �hesuguse omadusega nuppu (nt 4 heledat
v�i 4 pikka).

M�ngu alustamiseks annab arvuti kasutajale �he nupu algsest varust,
kus on k�ik 16 nuppu.

Igal korralisel k�igul paigutab m�ngija, kelle kord on, lauale
talle vastase poolt viimati antud nupu ning valib omakorda varust
v�lja j�rgmise nupu, mille annab vastasele.

St lauale paigutatakse mitte enda valitud, vaid vastase poolt valitud
nuppe.

V�idab m�ngija, kes saavutab suurima arvu t�isridu.

Arvutipoolne m�ngimine v�iks olla m��dukalt intelligentne.
Selleks v�iks arvuti m�ngu vaadata mingi arv k�ike ette.
Konfiguratsiooni hindeks v�iks olla edu vastase ees, mis on
saavutatud.



Sinu programm peab kasutama j�rgmisi t��bis�non��me
ja andmet��pe ning abifunktsioone:
-}

import Data.Char -- toUpper
-- import System.Random

data Color = Light | Dark
  deriving Eq

instance Show Color where
  show Light = "L"
  show Dark  = "D"

data Height = Short | Tall
  deriving Eq

instance Show Height where
  show Short = "S"
  show Tall  = "T"

data Shape = Round | Square
  deriving Eq

instance Show Shape where
  show Round  = "R"
  show Square = "Q"

data Fill = Filled | Hollow
  deriving Eq

instance Show Fill where
  show Filled = "F"
  show Hollow = "H"

type Fig = (Color, Height, Shape, Fill)

color  (c, _, _, _) = c
height (_, h, _, _) = h
shape  (_, _, s, _) = s
fill   (_, _, _, f) = f

showFig :: Fig -> String
showFig (c, h, s, f) = show c ++ show h ++ show s ++ show f


type Board = [[Maybe Fig]]    -- laua seis, 
                              -- 4x4 maatriks, osa pos v�ivad olla t�hjad

type Row = Int                -- t�isarv 0..3
type Col = Int                -- t�isarv 0..3
type Pos = (Row, Col)         -- positsioonid maatriksis

type Free = [Pos]             -- vabad pos, list max 16 elementi
    
type Avail = [Fig]            -- paigutamata nupud, list max 16 elementi   


data Player = User | Machine
  deriving Eq

type Score = Int

type Config = (Player, Fig, Board, Free, Avail, Score, Score)
                              -- konfiguratsioon:
                              -- m�ngija, kelle kord on,
                              -- nupp tema k�es,
                              -- lauaseis, vabad pos, paigutamata nupud
                              -- m�ngija tema vastase skoor

type Move = (Pos, Fig)        -- k�ik:
                              -- laua positsioon, kuhu m�ngija paneb nupu
                              -- nupp varust, mille m�ngija annab vastusele

data Tree = Node Config [(Move, Tree)] 
                              -- m�ngupuu:
                              -- igal tipul on
                              -- m�rgendiks jooksev konfiguratsioon
                              -- lasteks paarid (k�ik, alam-m�ngupuu)

                              
                              
-- abifunktsioonid
player (p, _, _, _, _, _, _) = p
fig    (_, f, _, _, _, _, _) = f
free   (_, _, _, f, _, _, _) = f
avail  (_, _, _, _, a, _, _) = a
winner (_, _, _, _, _, s1, s2) = case compare s1 s2 of
  EQ -> "Viik!"
  LT -> "Kaotasid!"
  GT -> "V�itsid!"

{-
1a. Kirjuta funktsioon initBoard, mis arvutab algse lauaseisu:
-}

initBoard :: Board
initBoard = [ [ Nothing | i <- [0..3] ] | j <- [0..3] ]

{-
1b. Kirjuta funktsioon initFree, mis arvutab algsed vabad positsioonid 
(= list k�igist positsioonidest laual):
-}

initFree :: Free
initFree = [ (r, c) | r <- [0..3], c <- [0..3] ]

{-
1c. Kirjuta funktsioon initAvail, mis arvutab algsed paigutamata nupud
(= list k�igist nuppudest, mis �ldse on olemas):
-}

initAvail :: Avail
initAvail = [ (c,h,s,f) | c <- [Light, Dark], h <- [Short, Tall], s <- [Round, Square], f <- [Filled, Hollow] ]

{-
1d. Kirjuta funktsioon initConfig, mis arvutab algse konfiguratsiooni:

(Kasutaja v�ib arvutilt saada �he kindla nupu. Pole tingimata
vaja genereerida juhuslikku nuppu.)
-}

initConfig :: Config
initConfig = ( User, figure, initBoard, initFree, newAvail, 0, 0) where
  avail = initAvail
  key = 0
  figure = (avail!!key)
  newAvail = [ i | i <- avail, i /= figure ]

{-
2a. Kirjuta funktsioon put, mis etteantud nupu paigutab etteantud
lauale etteantud positsiooni. Selle funktsiooni juures v�id
eeldada, et vastav positsioon on vaba.
-}

put :: Board -> Fig -> Pos -> Board 
put b fig (row, col) = take row b ++ [ newRow (b!!row) ] ++ drop (row+1) b where
  newRow old = (take col old) ++ [ Just fig ] ++ (drop (col+1) old)

{-
2b. Kirjuta funktsioon fullRow, mis etteantud nupu kohta rehkendab
v�lja, kas ja kui palju tema paigutamisel etteantud lauale etteantud
positsiooni tekib t�isridu. (V�id eeldada, et vastav positsioon
on vaba.)
-}

fullRow :: Board -> Fig -> Pos -> Int
fullRow b f (row, col) = points (b!!row) where
  points ((Just c0):(Just c1):(Just c2):(Just c3):_) = 
    if ( color c0 == color c1 && color c1 == color c2 && color c2 == color c3 ) ||
       ( height c0 == height c1 && height c1 == height c2 && height c2 == height c3 ) ||
       ( shape c0 == shape c1 && shape c1 == shape c2 && shape c2 == shape c3 ) ||
       ( fill c0 == fill c1 && fill c1 == fill c2 && fill c2 == fill c3 )
    then 2
    else 1
  points _ = 0

{-
Kirjuta kas analoogiline funktsioon fullCol, mis arvutab, kas ja
kui palju tekib t�isveerge.

(Nii fullRow kui ka fullRow v�ivad v��rtuseks anda 0, 1 v�i 2.)
-}

fullCol :: Board -> Fig -> Pos -> Int
fullCol b f (row, col) = fullRow [( ((b!!0)!!col):((b!!1)!!col):((b!!2)!!col):((b!!3)!!col):[] )] f (0,0) 

{-
2c. Kirjuta funktsioon move, mis sooritab etteantud k�igu --
ehk paari (positsioon, kuhu panna oma nupp, uus nupp, mis anda
vastasele) -- alusel rehkendab etteantud konfiguratsioonist v�lja
uue konfiguratsiooni, ehk teisis�nu sooritab k�igu.

Selle funktsiooni juures v�id eeldada, et k�ik on legaalne, st
etteantud positsioon on vaba ja etteantud nupp on paigutamata.
-}

move :: Config -> Move -> Config
move (p, f, b, free, a, s1, s2) (po, fi) = (p', f', b', free', a', s1', s2') where
  p'     = if p == User then Machine else User
  f'     = fi
  b'     = put b fi po
  free'  = [ i | i <- free, i /= po ]
  a'     = [ i | i <- a, i /= fi ]
  points = (fullRow b' f po) + (fullCol b' f po)
  s1'    = if p == User then s1+points else s1
  s2'    = if p == User then s2 else s2+points

{-
3a. Kirjuta funktsioon mkTree, mis genereerib etteantud
konfiguratsioonile vastava m�ngupuu.
-}

mkTree :: Config -> Tree
mkTree c = Node c []

{-
3b. Kirjuta funktsioon mkInitTree, mis genereerib
algkonfiguratsioonile vastava m�ngupuu.
-}

mkInitTree :: Tree
mkInitTree = mkTree initConfig

{-
4a. Kirjuta funktsioon negamax, mis etteantud konfiguratsiooni
j�rgi leiab "printsipaalse" k�igujada ja "maksimini", vaadates
etteantud m�ngupuus ette etteantud arv k�ike. Alustava
m�ngija tulemust (m�ngija edu vastase ees) tuleb maksimeerida,
vastase tulemust (vastase edu m�ngija ees) minimeerida (tema
tulemuse vastandarvu maksimeerida).

negamax :: Int -> Tree -> ([Move], Score)
-}

{-
4b. (Valik) Alternatiivselt v�ib negamax oma otsinguruumi piirata
nn alfa-beeta-k�rpimisega.
-}

{-
5a. Kirjuta funktsioon showBoard, mis teisendab lauaseisu stringiks
(ettevalmistusena kuvamiseks)
-}

showBoard :: Board -> String
showBoard board = p board 0 "\n       0      1      2      3\n     ------ ------ ------ ------\n" where
  l (Nothing:cs) s = l cs (s++"     | ")
  l (Just c:cs)  s = l cs (s++(showFig c)++" | ")
  l []           s = s
  p (r:rs)     i s = p rs (i+1) (s++"  "++(show i)++" | "++(l r "")++"\n")
  p []         _ s = s ++ "     ------ ------ ------ ------\n"

{-
5b. Kirjuta funktsioon showConfig, mis teisendab konfiguratsiooni stringiks.
-}

showConfig :: Config -> String
showConfig (p,f,b,free,a,s1,s2) = 
  showBoard b ++ 
  "     Nupp (" ++ (showFig f) ++ ")" ++
  "     Seis (" ++ show s1 ++ ":" ++ show s2 ++ ")\n" ++
  if length a > 0 then "     Vabad nupud: " ++ show [ showFig i | i <- a ] ++ "\n\n" else "\n"

{-
6a. Kirjuta funktsioon showMove, mis teisendab (arvuti) k�igu stringiks.
-}

showMove :: Move -> String
showMove ( (row, col) , fig) = "Rida " ++ show row ++ " tulp " ++ show col ++ ", Sulle annan nupu " ++(showFig fig)

{-
6b. Kirjuta funktsioon readMove, mis teisendab stringi k�iguks
(m�ngija k�iguks), kui string on sellena t�lgendatav.

(K�ik v�iks lihtsuse m�ttes olla esitatud kujul "3 2 LTRH" -
rida, veerg, nuppu iseloomustavad omadused.)
-}

readMove :: String -> Maybe Move
readMove (row:_:col:_:c:h:s:f:_) = mov where
  row' = read [row]::Int
  col' = read [col]::Int
  c'   = if [toUpper c] == "L" then Light else Dark
  h'   = if [toUpper h] == "S" then Short else Tall
  s'   = if [toUpper s] == "R" then Round else Square
  f'   = if [toUpper f] == "F" then Filled else Hollow
  mov = if row' > 3 || col' > 3
    then Nothing
    else Just ((row',col'), (c', h', s', f'))
readMove _ = Nothing

{-
6c. Kirjuta funktsioon moveOk, mis kontrollib, kas etteantud k�ik
on v�imalik:
-}
moveOk :: Free -> Avail -> Move -> Bool
moveOk free avail (pos, fig) = fun [ i | i <- free, i == pos] [ i | i <- avail, i == fig] where
  fun arr1 arr2 = if (length arr1) == 1 && (length arr2) == 1 then True else False

{-
7a. Kirjuta funktsioon play, mis m�ngib m�ngu etteantud m�ngupuu
p�hjal.

Funktsioon peaks t��tama nii: 
 - kuvab jooksva konfiguratsiooni
 - kui m�ng on l�ppenud, teatab, kumb v�itis
 - kui on kasutaja (User) kord, siis k�sib talt k�igu valikut
   (korrates k�simust, kui m�ngija sisestab midagi loetamatut
   v�i k�igu, mis pole v�imalik), ning j�tkab vastavast
   alampuust
 - kui on arvuti (Machine) kord, siis arvutab arvuti valiku k�igu
   osas (kasutades negamax mingi madala s�gavusega), kuvab valitud
   k�igu ning j�tkab vastavast alampuust.

V�ib proovida ettevaatamise s�gavust m�ngu l�pu poole suurendada.
-}


play :: Tree -> IO ()
play t@(Node conf sub) = 
  do
    putStr (showConfig conf)
    if (length (free conf)) == 0 
      then
        putStr ("M�ng l�bi! " ++ winner conf ++ "\n")
      else
        if (length (avail conf)) == 0 
          then do
            putStr "... ja viimane nupp lauale\n"
            play ( mkTree (move conf ( ((free conf)!!0), (fig conf))) )
          else
            if (player conf) == User 
              then do
                putStr "Tee oma k�ik:\n"
                
                {-
                next <- getLine
                l�heb loopi Windows 7 + WinHugs Version: Sep 2006
                teeme ajutise h�ki ja v�tame esimese nupu ja vaba positsiooni
                -}
                
                play ( mkTree (move conf ( ((free conf)!!0), ((avail conf)!!0))) )
              else do
                putStr ("Minu k�ik on: " ++ (showMove ( ((free conf)!!0), ((avail conf)!!0)) ) ++ "\n")
                play ( mkTree (move conf ( ((free conf)!!0), ((avail conf)!!0))) )


{-
7b. Kirjuta ka funktsioon initPlay (peafunktsioon), mis m�ngib m�ngu
algusest.
-}
initPlay :: IO ()
initPlay = play mkInitTree

{-

Main> initPlay

       0      1      2      3
     ------ ------ ------ ------
  0 |      |      |      |      | 
  1 |      |      |      |      | 
  2 |      |      |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (LSRF)     Seis (0:0)
     Vabad nupud: ["LSRH","LSQF","LSQH","LTRF","LTRH","LTQF","LTQH","DSRF","DSRH","DSQF","DSQH","DTRF","DTRH","DTQF","DTQH"]

Tee oma k�ik:

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH |      |      |      | 
  1 |      |      |      |      | 
  2 |      |      |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (LSRH)     Seis (0:0)
     Vabad nupud: ["LSQF","LSQH","LTRF","LTRH","LTQF","LTQH","DSRF","DSRH","DSQF","DSQH","DTRF","DTRH","DTQF","DTQH"]

Minu k�ik on: Rida 0 tulp 1, Sulle annan nupu LSQF

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF |      |      | 
  1 |      |      |      |      | 
  2 |      |      |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (LSQF)     Seis (0:0)
     Vabad nupud: ["LSQH","LTRF","LTRH","LTQF","LTQH","DSRF","DSRH","DSQF","DSQH","DTRF","DTRH","DTQF","DTQH"]

Tee oma k�ik:

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH |      | 
  1 |      |      |      |      | 
  2 |      |      |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (LSQH)     Seis (0:0)
     Vabad nupud: ["LTRF","LTRH","LTQF","LTQH","DSRF","DSRH","DSQF","DSQH","DTRF","DTRH","DTQF","DTQH"]

Minu k�ik on: Rida 0 tulp 3, Sulle annan nupu LTRF

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 |      |      |      |      | 
  2 |      |      |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (LTRF)     Seis (0:2)
     Vabad nupud: ["LTRH","LTQF","LTQH","DSRF","DSRH","DSQF","DSQH","DTRF","DTRH","DTQF","DTQH"]

Tee oma k�ik:

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH |      |      |      | 
  2 |      |      |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (LTRH)     Seis (0:2)
     Vabad nupud: ["LTQF","LTQH","DSRF","DSRH","DSQF","DSQH","DTRF","DTRH","DTQF","DTQH"]

Minu k�ik on: Rida 1 tulp 1, Sulle annan nupu LTQF

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF |      |      | 
  2 |      |      |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (LTQF)     Seis (0:2)
     Vabad nupud: ["LTQH","DSRF","DSRH","DSQF","DSQH","DTRF","DTRH","DTQF","DTQH"]

Tee oma k�ik:

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF | LTQH |      | 
  2 |      |      |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (LTQH)     Seis (0:2)
     Vabad nupud: ["DSRF","DSRH","DSQF","DSQH","DTRF","DTRH","DTQF","DTQH"]

Minu k�ik on: Rida 1 tulp 3, Sulle annan nupu DSRF

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF | LTQH | DSRF | 
  2 |      |      |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (DSRF)     Seis (0:3)
     Vabad nupud: ["DSRH","DSQF","DSQH","DTRF","DTRH","DTQF","DTQH"]

Tee oma k�ik:

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF | LTQH | DSRF | 
  2 | DSRH |      |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (DSRH)     Seis (0:3)
     Vabad nupud: ["DSQF","DSQH","DTRF","DTRH","DTQF","DTQH"]

Minu k�ik on: Rida 2 tulp 1, Sulle annan nupu DSQF

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF | LTQH | DSRF | 
  2 | DSRH | DSQF |      |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (DSQF)     Seis (0:3)
     Vabad nupud: ["DSQH","DTRF","DTRH","DTQF","DTQH"]

Tee oma k�ik:

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF | LTQH | DSRF | 
  2 | DSRH | DSQF | DSQH |      | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (DSQH)     Seis (0:3)
     Vabad nupud: ["DTRF","DTRH","DTQF","DTQH"]

Minu k�ik on: Rida 2 tulp 3, Sulle annan nupu DTRF

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF | LTQH | DSRF | 
  2 | DSRH | DSQF | DSQH | DTRF | 
  3 |      |      |      |      | 
     ------ ------ ------ ------
     Nupp (DTRF)     Seis (0:5)
     Vabad nupud: ["DTRH","DTQF","DTQH"]

Tee oma k�ik:

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF | LTQH | DSRF | 
  2 | DSRH | DSQF | DSQH | DTRF | 
  3 | DTRH |      |      |      | 
     ------ ------ ------ ------
     Nupp (DTRH)     Seis (2:5)
     Vabad nupud: ["DTQF","DTQH"]

Minu k�ik on: Rida 3 tulp 1, Sulle annan nupu DTQF

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF | LTQH | DSRF | 
  2 | DSRH | DSQF | DSQH | DTRF | 
  3 | DTRH | DTQF |      |      | 
     ------ ------ ------ ------
     Nupp (DTQF)     Seis (2:7)
     Vabad nupud: ["DTQH"]

Tee oma k�ik:

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF | LTQH | DSRF | 
  2 | DSRH | DSQF | DSQH | DTRF | 
  3 | DTRH | DTQF | DTQH |      | 
     ------ ------ ------ ------
     Nupp (DTQH)     Seis (4:7)

... ja viimane nupp lauale

       0      1      2      3
     ------ ------ ------ ------
  0 | LSRH | LSQF | LSQH | LTRF | 
  1 | LTRH | LTQF | LTQH | DSRF | 
  2 | DSRH | DSQF | DSQH | DTRF | 
  3 | DTRH | DTQF | DTQH | DTQH | 
     ------ ------ ------ ------
     Nupp (DTQH)     Seis (4:10)

M�ng l�bi! Kaotasid!

Main> readMove "1 2 dsqh"
Just ((1,2),(D,S,Q,H))

-}

