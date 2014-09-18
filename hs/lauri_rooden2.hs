
-- 2. kodu�lesanne Lauri Rooden DK21

{-
programm peab kasutama j�rgmisi t��bis�non��me
ja andmet��pe ning abifunktsioone:
-}

data Color = Light | Dark

instance Show Color where
  show Light = "L"
  show Dark  = "D"

data Height = Short | Tall

instance Show Color where
  show Short = "S"
  show Tall  = "T"

data Shape = Round | Square

instance Show Shape where
  show Round  = "R"
  show Square = "Q"

data Fill = Filled | Hollow

instance Show Fill where
  show Solid  = "F"
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


{-
1a. Kirjuta funktsioon initBoard, mis arvutab algse lauaseisu:

initBoard :: Board

1b. Kirjuta funktsioon initFree, mis arvutab algsed vabad positsioonid 
(= list k�igist positsioonidest laual):

initFree :: Free

1c. Kirjuta funktsioon initAvail, mis arvutab algsed paigutamata nupud
(= list k�igist nuppudest, mis �ldse on olemas):

initAvail :: Avail

1d. Kirjuta funktsioon initConfig, mis arvutab algse konfiguratsiooni:

initConfig :: Config

(Kasutaja v�ib arvutilt saada �he kindla nupu. Pole tingimata
vaja genereerida juhuslikku nuppu.)
-}


{-
2a. Kirjuta funktsioon put, mis etteantud nupu paigutab etteantud
lauale etteantud positsiooni. Selle funktsiooni juures v�id
eeldada, et vastav positsioon on vaba.

put :: Board -> Fig -> Pos -> Board 

2b. Kirjuta funktsioon fullRow, mis etteantud nupu kohta rehkendab
v�lja, kas ja kui palju tema paigutamisel etteantud lauale etteantud
positsiooni tekib t�isridu. (V�id eeldada, et vastav positsioon
on vaba.)

fullRow :: Board -> Fig -> Pos -> Int

Kirjuta kas analoogiline funktsioon fullCol, mis arvutab, kas ja
kui palju tekib t�isveerge.

fullCol :: Board -> Fig -> Pos -> Int

(Nii fullRow kui ka fullRow v�ivad v��rtuseks anda 0, 1 v�i 2.)

2c. Kirjuta funktsioon move, mis sooritab etteantud k�igu --
ehk paari (positsioon, kuhu panna oma nupp, uus nupp, mis anda
vastasele) -- alusel rehkendab etteantud konfiguratsioonist v�lja
uue konfiguratsiooni, ehk teisis�nu sooritab k�igu.

move :: Config -> Move -> Config

Selle funktsiooni juures v�id eeldada, et k�ik on legaalne, st
etteantud positsioon on vaba ja etteantud nupp on paigutamata.


-}

{-
3a. Kirjuta funktsioon mkTree, mis genereerib etteantud
konfiguratsioonile vastava m�ngupuu.

mkTree :: Config -> Tree

3b. Kirjuta funktsioon mkInitTree, mis genereerib
algkonfiguratsioonile vastava m�ngupuu.

mkInitTree :: Tree
mkInitTree = mkTree initConfig

-}

{-

4a. Kirjuta funktsioon negamax, mis etteantud konfiguratsiooni
j�rgi leiab "printsipaalse" k�igujada ja "maksimini", vaadates
etteantud m�ngupuus ette etteantud arv k�ike. Alustava
m�ngija tulemust (m�ngija edu vastase ees) tuleb maksimeerida,
vastase tulemust (vastase edu m�ngija ees) minimeerida (tema
tulemuse vastandarvu maksimeerida).

negamax :: Int -> Tree -> ([Move], Score)

4b. (Valik) Alternatiivselt v�ib negamax oma otsinguruumi piirata
nn alfa-beeta-k�rpimisega.


-}

{-

5a. Kirjuta funktsioon showBoard, mis teisendab lauaseisu stringiks
(ettevalmistusena kuvamiseks)

showBoard :: Board -> String

5b. Kirjuta funktsioon showConfig, mis teisendab konfiguratsiooni
stringiks.

showConfig :: Config -> String


-}

{-

6a. Kirjuta funktsioon showMove, mis teisendab k�igu (arvuti
k�igu) stringiks.

showMove :: Move -> String

6b. Kirjuta funktsioon readMove, mis teisendab stringi k�iguks
(m�ngija k�iguks), kui string on sellena t�lgendatav.

readMove :: String -> Maybe Move

(K�ik v�iks lihtsuse m�ttes olla esitatud kujul "3 2 LTRH" -
rida, veerg, nuppu iseloomustavad omadused.)

6c. Kirjuta funktsioon moveOk, mis kontrollib, kas etteantud k�ik
on v�imalik:

moveOk: Free -> Avail -> Move -> Bool

-}

{-

7a. Kirjuta funktsioon play, mis m�ngib m�ngu etteantud m�ngupuu
p�hjal.

play :: Tree -> IO ()

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


7b. Kirjuta ka funktsioon initPlay (peafunktsioon), mis m�ngib m�ngu
algusest.

initPlay :: IO ()
initPlay = play mkInitTree

-}


