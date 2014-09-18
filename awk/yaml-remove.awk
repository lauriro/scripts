#!/usr/bin/awk -f
 
# Remove YAML front matter from a jekyll post
 
BEGIN { drop = 0; }
/^---/ {if(NR == 1) {drop = 1} else {drop = 0; next} }

drop == 0 {print}

