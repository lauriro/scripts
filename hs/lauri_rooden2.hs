
-- 2. koduülesanne Lauri Rooden DK21

{-
programm peab kasutama järgmisi tüübisünonüüme
ja andmetüüpe ning abifunktsioone:
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
                              -- 4x4 maatriks, osa pos võivad olla tühjad

type Row = Int                -- täisarv 0..3
type Col = Int                -- täisarv 0..3
type Pos = (Row, Col)         -- positsioonid maatriksis

type Free = [Pos]             -- vabad pos, list max 16 elementi
    
type Avail = [Fig]            -- paigutamata nupud, list max 16 elementi   


data Player = User | Machine

type Score = Int

type Config = (Player, Fig, Board, Free, Avail, Score, Score)
                              -- konfiguratsioon:
                              -- mängija, kelle kord on,
                              -- nupp tema käes,
                              -- lauaseis, vabad pos, paigutamata nupud
                              -- mängija tema vastase skoor

type Move = (Pos, Fig)        -- käik:
                              -- laua positsioon, kuhu mängija paneb nupu
                              -- nupp varust, mille mängija annab vastusele

data Tree = Node Config [(Move, Tree)] 
                              -- mängupuu:
                              -- igal tipul on
                              -- märgendiks jooksev konfiguratsioon
                              -- lasteks paarid (käik, alam-mängupuu)


{-
1a. Kirjuta funktsioon initBoard, mis arvutab algse lauaseisu:

initBoard :: Board

1b. Kirjuta funktsioon initFree, mis arvutab algsed vabad positsioonid 
(= list kõigist positsioonidest laual):

initFree :: Free

1c. Kirjuta funktsioon initAvail, mis arvutab algsed paigutamata nupud
(= list kõigist nuppudest, mis üldse on olemas):

initAvail :: Avail

1d. Kirjuta funktsioon initConfig, mis arvutab algse konfiguratsiooni:

initConfig :: Config

(Kasutaja võib arvutilt saada ühe kindla nupu. Pole tingimata
vaja genereerida juhuslikku nuppu.)
-}


{-
2a. Kirjuta funktsioon put, mis etteantud nupu paigutab etteantud
lauale etteantud positsiooni. Selle funktsiooni juures võid
eeldada, et vastav positsioon on vaba.

put :: Board -> Fig -> Pos -> Board 

2b. Kirjuta funktsioon fullRow, mis etteantud nupu kohta rehkendab
välja, kas ja kui palju tema paigutamisel etteantud lauale etteantud
positsiooni tekib täisridu. (Võid eeldada, et vastav positsioon
on vaba.)

fullRow :: Board -> Fig -> Pos -> Int

Kirjuta kas analoogiline funktsioon fullCol, mis arvutab, kas ja
kui palju tekib täisveerge.

fullCol :: Board -> Fig -> Pos -> Int

(Nii fullRow kui ka fullRow võivad väärtuseks anda 0, 1 või 2.)

2c. Kirjuta funktsioon move, mis sooritab etteantud käigu --
ehk paari (positsioon, kuhu panna oma nupp, uus nupp, mis anda
vastasele) -- alusel rehkendab etteantud konfiguratsioonist välja
uue konfiguratsiooni, ehk teisisõnu sooritab käigu.

move :: Config -> Move -> Config

Selle funktsiooni juures võid eeldada, et käik on legaalne, st
etteantud positsioon on vaba ja etteantud nupp on paigutamata.


-}

{-
3a. Kirjuta funktsioon mkTree, mis genereerib etteantud
konfiguratsioonile vastava mängupuu.

mkTree :: Config -> Tree

3b. Kirjuta funktsioon mkInitTree, mis genereerib
algkonfiguratsioonile vastava mängupuu.

mkInitTree :: Tree
mkInitTree = mkTree initConfig

-}

{-

4a. Kirjuta funktsioon negamax, mis etteantud konfiguratsiooni
järgi leiab "printsipaalse" käigujada ja "maksimini", vaadates
etteantud mängupuus ette etteantud arv käike. Alustava
mängija tulemust (mängija edu vastase ees) tuleb maksimeerida,
vastase tulemust (vastase edu mängija ees) minimeerida (tema
tulemuse vastandarvu maksimeerida).

negamax :: Int -> Tree -> ([Move], Score)

4b. (Valik) Alternatiivselt võib negamax oma otsinguruumi piirata
nn alfa-beeta-kärpimisega.


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

6a. Kirjuta funktsioon showMove, mis teisendab käigu (arvuti
käigu) stringiks.

showMove :: Move -> String

6b. Kirjuta funktsioon readMove, mis teisendab stringi käiguks
(mängija käiguks), kui string on sellena tõlgendatav.

readMove :: String -> Maybe Move

(Käik võiks lihtsuse mõttes olla esitatud kujul "3 2 LTRH" -
rida, veerg, nuppu iseloomustavad omadused.)

6c. Kirjuta funktsioon moveOk, mis kontrollib, kas etteantud käik
on võimalik:

moveOk: Free -> Avail -> Move -> Bool

-}

{-

7a. Kirjuta funktsioon play, mis mängib mängu etteantud mängupuu
põhjal.

play :: Tree -> IO ()

Funktsioon peaks töötama nii: 

- kuvab jooksva konfiguratsiooni
 
- kui mäng on lõppenud, teatab, kumb võitis

- kui on kasutaja (User) kord, siis küsib talt käigu valikut
  (korrates küsimust, kui mängija sisestab midagi loetamatut
  või käigu, mis pole võimalik), ning jätkab vastavast
  alampuust

- kui on arvuti (Machine) kord, siis arvutab arvuti valiku käigu
  osas (kasutades negamax mingi madala sügavusega), kuvab valitud
  käigu ning jätkab vastavast alampuust.

Võib proovida ettevaatamise sügavust mängu lõpu poole suurendada.


7b. Kirjuta ka funktsioon initPlay (peafunktsioon), mis mängib mängu
algusest.

initPlay :: IO ()
initPlay = play mkInitTree

-}


