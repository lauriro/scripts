#Recursive wildcards can be done purely in Make, without calling the shell or the find command. Doing the search using only Make means that this solution works on Windows as well, not just *nix.

# Make does not offer a recursive wildcard function, so here's one:
rwildcard=$(wildcard $1$2) $(foreach d,$(wildcard $1*),$(call rwildcard,$d/,$2))

# How to recursively find all files with the same name in a given folder
ALL_INDEX_HTMLS := $(call rwildcard,foo/,index.html)

# How to recursively find all files that match a pattern
ALL_HTMLS := $(call rwildcard,foo/,*.html)


 	
# It support multi directory in $1 – netawater Apr 16 at 13:54
rwildcard=$(wildcard $(addsuffix $2, $1)) $(foreach d,$(wildcard $(addsuffix *, $1)),$(call rwildcard,$d/,$2))


