
cp:
	find . -maxdepth 1 -type d -name "*-lite" -exec cp Makefile {}/ \; -exec cp make-readme-files.mk {}/ \;
