

PROGS = yaml-remove.awk ascii-table-to-html.awk
engine = Makefile $(PROGS)


all: $(wildcard tests/*.html)
	# Make $(wildcard tests/*.html)

%.html: %.md $(engine)
	# Build $@
	@cat $< | tr -d "\r" $(patsubst %,| awk -f %,$(PROGS)) > $@
	@cat $@

