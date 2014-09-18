
command -v apt-get && {
	echo "apt-get exists" 1>&2
	exit 1
}

test $EUID -ne 0 && {
	echo "This script must be run as root" 1>&2
	exit 1
}

FILE=/usr/bin/pacman-cheat-sheet


cat > $FILE <<EOF
cat <<EOD
Arch Linux uses pacman as package manager

Instructions on how to use pacman
---------------------------------

  pacman -Sy                     # Update local package database
  pacman -Qu                     # List outdated packages
  pacman -S package_name1 ...    # Install packages (including dependencies)
  pacman -S extra/package_name   # Install from specific repository
  
  pacman -R package_name         # Remove a package (leaving dependencies)
  pacman -Rs package_name        # Remove with dependencies
  pacman -Rn package_name        # Remove with configuration backups (.pacsave) 
  
  pacman -Ss string1 ...         # Search in packages' names and descriptions
  pacman -Qs string1 ...         # Search already installed packages
  pacman -Si package_name        # Display extensive information about a package
  pacman -Qi package_name        # ... and for locally installed packages
  pacman -Ql package_name        # List of the files installed by a package
  
  pacman -Qo /path/to/file       # Into which package a file belongs to
  pacman -Qdt                    # Packages no longer required as dependencies
  pactree package_name           # To list a dependency tree of a package
  
  whoneeds package_name          # Packages depending on an installed package
                                 # (uses whoneeds from pkgtools)
  
# To downgrade the package visit /var/cache/pacman/pkg on your system 
# and seeing if the older version of the package is stored there. 
# (If you have not run pacman -Scc recently, it should be there). 
# If the package is there, you can install that version using 
pacman -U /var/cache/pacman/pkg/pkgname-olderpkgver.pkg.tar.gz

See more:
  https://wiki.archlinux.org/index.php/Pacman
  https://wiki.archlinux.org/index.php/Pacman_Tips

EOD
EOF

ln -s $FILE /usr/bin/apt-get       # Debian/Ubuntu
ln -s $FILE /usr/bin/apt-cache
ln -s $FILE /usr/bin/yum           # Red Hat/Fedora
ln -s $FILE /usr/bin/rug           # (Old) SUSE
ln -s $FILE /usr/bin/zypper        # openSUSE
ln -s $FILE /usr/bin/emerge        # Gentoo

chmod +x $FILE /usr/bin/apt-get /usr/bin/apt-cache /usr/bin/yum /usr/bin/rug /usr/bin/zypper /usr/bin/emerge   



