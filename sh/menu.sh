#!/bin/sh
/bin/echo "Welcome!
Your choices are:
 
1       See today's date
2       See who's logged in
3       See current processes
q       Quit"
 
/bin/echo "Your choice: \c"
read ans
while [ "$ans" != "q" ]
do
  case "$ans" in
    1)
        /bin/date
        ;;
    2)
        /bin/who
        ;;
    3)
        /usr/ucb/w
        ;;
    q)
        /bin/echo "Goodbye"
        exit 0
        ;;
    *)
        /bin/echo "Invalid choice '$ans': please try again"
        ;;
  esac
  /bin/echo "Your choice: \c"
  read ans
done
exit 0
#/bin/echo Sorry, buddy, but you've been terminated!
