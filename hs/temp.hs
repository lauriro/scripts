
add :: Int -> Int
add x = x + 1



fact :: Integer -> Integer
fact 0 = 1
-- fact (x + 1) = fact x * (x + 1)
fact x = fact (x-1) * x



factIf :: Integer -> Integer
factIf x = if x == 0 then 1 else fact (x - 1) * x



factGuard :: Integer -> Integer
factGuard x | x == 0    = 1
factGuard x | otherwise = factGuard (x - 1) * x



factAcc :: Integer -> Integer
factAcc x = f 1 x where
    f acc 0       = acc
    f acc (x + 1) = f (acc * (x + 1)) x



factNeg :: Integer -> Integer
factNeg x = f 1 x where
    f acc 0 = acc
    f acc x = f (acc * (x + 1)) x



h :: Int -> Int -> Int
h x y = (x - y) * 7
-- prefill, tagastatud funktsioon täidetakse
h2 = h 2



doubMap :: [Int] -> [Int]
-- doubMap xs = map (*2) xs
-- doubMap xs = map h2 xs
doubMap xs = map (`div`2) xs



len :: [a] -> Int
len [] = 0
len (x:xs) = 1 + len xs



mymap :: (a -> b) -> [a] -> [b]
mymap f [] = []
mymap f (x:xs) = f x : mymap f xs
-- > mymap (*2) [1,2,3]
-- [2,4,6]



lengthen :: [a] -> [a]
-- > lengthen [1,2,6,4]
-- [1,1,2,2,6,6,4,4]
lengthen [] = []
lengthen (x:xs) = x:x:lengthen xs



shorten :: [a] -> [a]
-- > shorten [1,2,4,6]
-- [1,6]
-- > shorten [1,2,6,4,5]
-- [1,6,5]
shorten [] = []
shorten [x] = [x]
shorten (x:y:xs) = x:shorten xs



dropInitZeros :: [Integer] -> [Integer]
-- > dropInitZeros [0,0,17,0,5]
-- [17,0,5]
dropInitZeros [] = []
dropInitZeros (0:xs) = dropInitZeros xs
dropInitZeros xs = xs



sums :: [Integer] -> [Integer]
-- > sums [1,4,2,6,3,8,5]
-- [5,6,8,9,11,13]
sums [] = []
sums [x] = []
sums (x0:x1:xs) = x0 + x1 : sums (x1:xs)



fib :: Integer -> Integer
fib 0 = 0
fib 1 = 1
fib (x+2) = fib x + fib (x+1)

fib2 :: Integer -> (Integer, Integer)
fib2 0       = (0, 0)
fib2 (x + 1) = (f+f', f) where
                            (f, f') = fib2 x

fibl :: [Integer]
fibl = 0:1:zipWith (+) (tail fibl) fibl
-- > take 21 fibl
-- [0,1,1,2,3,5,8,13,21,34,55,89,144,233,377,610,987,1597,2584,4181,6765]
-- > fibl !! 20
-- 6765


{-
mymap :: (a -> b) -> [a] -> [b]
mymap f [] = []
mymap f (x:xs) = f x : mymap f xs

factAcc :: Integer -> Integer
factAcc x = f 1 x where
    f acc 0       = acc
    f acc (x + 1) = f (acc * (x + 1)) x

factIf x = if x == 0 then 1 else fact (x - 1) * x

factGuard x | x == 0    = 1
factGuard x | otherwise = factGuard (x - 1) * x

-}





-- Koduülesanded Lauri Rooden 

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
	sign x = if x < 0 then -1 else 1
	f [] out [] _ = out
	f [] out seg _ = out ++ [seg]
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
	f [] suf = suf ++ [[]]
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





ss x y z = x z (y z)
kk x y = x

f :: Int -> Int -> [Int]
f i j = if i <= 0 then [] else i : j : f (i-j) (j+1)


merge :: [a] -> [a] -> [a]
merge [] a = a
merge (x:xs) ys = x : merge ys xs

merge3 :: [a] -> [a] -> [a] -> [a]
merge3 a b c = merge a (merge b c)

(+++) :: [a] -> [a] -> [a]
a +++ [] = a
[] +++ b = b
(x:xs) +++ (y:ys) = x : y : (xs +++ ys)

data BExp = X | TT | FF | Not BExp | And BExp BExp | Or BExp BExp

-- bEval : BExp -> Bool -> Bool




















