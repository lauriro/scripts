

Table with header

+---------------+:--------------:+--------------:+
| Left-Aligned  | Center Aligned | Right Aligned |
+---------------+----------------+--------------:+
| row 1 col 1   | row 1 col 2    |            $1 |
| row 2 col 1   | row 2 col 2    |            $2 |
| row 3 col 1   | row 3 col 2    |            $3 |
| row 4 col 1   | row 4 col 2    |            $4 |
+---------------+----------------+---------------+

Table without header

+---------------+----------------+--------------:+
| row 1 col 1   | row 1 col 2    |            $1 |
| row 2 col 1   | row 2 col 2    |            $2 |
| row 3 col 1   | row 3 col 2    |            $3 |
| row 4 col 1   | row 4 col 2    |            $4 |
+---------------+----------------+---------------+

Table without endings

---------------+----------------+--------------:
 row 1 col 1   | row 1 col 2    |            $1 
 row 2 col 1   | row 2 col 2    |            $2 
 row 3 col 1   | row 3 col 2    |            $3 
 row 4 col 1   | row 4 col 2    |            $4 
---------------+----------------+---------------

| Left-Aligned  | Center Aligned  | Right Aligned |
| :------------ |:---------------:| -----:|
| col 3 is      | some wordy text | $1600 |
| col 2 is      | centered        |   $12 |
| zebra stripes | are neat        |    $1 |



|+ The table's caption


Note: The <colgroup> tag must be a child of a <table> element, after any <caption> elements and before any <thead>, <tbody>, <tfoot>, and <tr> elements.

<table>
  <colgroup>
    <col span="2" style="background-color:red">
    <col style="background-color:yellow">
  </colgroup>
  <tr>
    <th>ISBN</th>
    <th>Title</th>
    <th>Price</th>
  </tr>
  <tr>
    <td>3476896</td>
    <td>My first HTML</td>
    <td>$53</td>
  </tr>
  <tr>
    <td>5869207</td>
    <td>My first CSS</td>
    <td>$49</td>
  </tr>
</table>
