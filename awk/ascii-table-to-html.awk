


BEGIN {
	FS  = "|"
	row = 0
	out = ""
}

/^$/ && row != 0 {
	print out "\n</table>"
	row = 0
	out = ""
}

/^\+([-:]+\+)+$/ {
	if (row == 0) print "<table>"
	for (i = split($0, align, "+"); i-- > 0;) {
		align[i] = match(align[i], ":$") ? match(align[i], "^:") ? " class=mid" : " class=rgt" : ""
	}
	row++
	next
}

row != 0 {
	if (out != "") {
		if (row > 1) {
			gsub(/td>/, "th>", out)
			row = 1
		}
		print out
	}
	gsub(/</, "\\&lt;")
	gsub(/>/, "\\&gt;")
	gsub(/&/, "\\&gt;")

	out = "<tr>"
	for (f = 2; f < NF; f++) {
		gsub(/^[ \t]+|[ \t]+$/, "", $f)
		out = out "<td" align[f] ">" $f "</td>"
	}
	out = out "</tr>"
}

row == 0

