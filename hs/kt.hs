
splitBy :: [Bool] -> [a] -> ([a],[a])
splitBy  x y = f x y [] [] where
  f (True:xs) (y0:ys) tru fal = f xs ys (tru ++ [y0]) fal
  f (False:xs) (y0:ys) tru fal = f xs ys tru (fal ++ [y0])
  f _ _ tru fal      = (tru, fal)

	
data Tree a = Leaf | Bin a (Tree a) (Tree a)

paths :: Tree a -> [[a]]
paths x = f x [] where
	f Leaf [] = [[]]
	f Leaf a = a
	f (Bin b c d) a = [ f c (a ++ [b]) ] 


{-
paths :: Tree a -> [[a]]
paths Leaf = [[]]
paths (Bin a b c) = [ [a] ++ paths b ]
-}

