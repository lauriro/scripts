Function vbBinToArr(bArr)
  Dim i, l
  l=LenB(bArr)
  ReDim arr(l)
  For i=1 To l
    arr(i-1)=AscB(MidB(bArr,i,1))
  Next
  vbBinToArr = arr
End Function
