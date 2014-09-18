
-- Koduülesanded Lauri Rooden DK21

{-
1. Kirjuta funktsioon splitBySign, mis listi täisarve jaotab kaheks
   listiks, kuhu paigutab selle listi vastavalt negatiivsed ning
   postiivsed (sh 0) elemendid, algses järjekorras, st

   splitBySign :: [Int] -> ([Int], [Int])

   > splitBySign [17,-1,-13,28,1,0,2,-6,-34,37,15,-3,48]
   ([17,28,1,0,2,37,15,48],[-1,-13,-6,-34,-3]]

   Püüa see funktsioon kirjutada nii, et etteantud listi
   vaadataks läbi ainult üks üks kord.
-}
splitBySign :: [Int] -> ([Int], [Int])
splitBySign x = f x [] [] where
    f [] pos neg      = (pos, neg)
    f (x0:xs) pos neg = if x0 < 0 then f xs pos (neg ++ [x0]) else f xs (pos ++ [x0]) neg


{-
2. Kirjuta funktsioon sameSignSegments, mis võtab listi arve ja
   lõikab ta maksimaalseteks (st võimalikult pikkadeks)
   ühemärgiliste (positiivsete, sh 0, ning negatiivsete) arvude
   segmentideks, st

   sameSignSegments :: [Int] -> [[Int]]

   > sameSignSegments [17,-1,-13,28,1,0,2,-6,-34,37,15,-3,48]
   [[17],[-1,-13],[28,1,0,2],[-6,-34],[37,15],[-3],[48]]
-}
sameSignSegments :: [Int] -> [[Int]]
sameSignSegments x = f x [] [] (sign (head x)) where
    {- sign tagastab -1 negatiivse ja 1 positiivse ning 0 korral.
       prelude's olev signum ei sobi, kuna tagastab 0 korral 0 -}
    sign x                = if x < 0 then -1 else 1
    f [] out [] _         = out
    f [] out seg _        = out ++ [seg]
    f (x0:xs) out seg sig = if (sign x0) == sig then f xs out (seg ++ [x0]) sig else f xs (out ++ [seg]) [x0] (sign x0)



{-
3. Kirjuta funktsioon suffixes, mis võtab listi elemente ning
   tagastab selles listi kõik sufiksid, st

   suffixes :: [a] -> [[a]]
   
   > suffixes "hello!"
   ["hello!","ello!","llo!","lo!","o!","!",""]
-}
suffixes :: [a] -> [[a]]
suffixes x = f x [] where
    f [] suf      = suf ++ [[]]
    f (x0:xs) suf = f xs (suf ++ [x0:xs])


{-
4. Kirjuta funktsioon shuffle, mis etteantud stringi iga
   3. tähemärgi järele lisab string "pi", st 

   pipi :: String -> String

   > pipi "My homework..."
   "My pihompiewopirk.pi.."
-}
pipi :: String -> String
pipi (x0:x1:x2:xs) = [x0, x1, x2] ++ "pi" ++ pipi xs
pipi x = x


{-
5. Kirjuta funktsioon, mis võtab täisarvu ja tagastab vastava
   rea Pascali kolmnurgast:
 
   0 |  1  0  0  0  0  0  0  0  ...
   1 |  1  1  0  0  0  0  0  0  ...
   2 |  1  2  1  0  0  0  0  0  ...
   3 |  1  3  3  1  0  0  0  0  ...
   4 |  1  4  6  4  1  0  0  0  ...
   5 |  1  5 10 10  5  1  0  0  ...
   ...

   Iga rea $n$-es element võrdub eelmise rea $n-1$ ja $n$-nda
    elemendi summaga.

   pascal :: Int -> [Int]
   > pascal 7
   [1,7,21,35,35,21,7,1,0,0,0,0,0,..

   Soovitus: Kasuta funktsiooni zipWith.
-}
pascal :: Int -> [Int]
pascal x = (pascalList !! x) ++ [0,0..1] where
    pascalList = iterate (\row -> zipWith (+) ([0] ++ row) (row ++ [0])) [1]
{- Kuna liidame lõpmatu listi otsa, sobib testimiseks paremini 
   > take 13 (pascal 7)
   [1,7,21,35,35,21,7,1,0,0,0,0,0]
-}




