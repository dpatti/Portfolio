RM=rm -f
LIBPATH=../lib/
CFLAGS=-pedantic -Wall -I${LIBPATH} -std=c99
LDFLAGS=-L${LIBPATH} -I${LIBPATH}
LDLIBS=-lmy

all: window.c
	cc window.c -o window ${CFLAGS} ${LDFLAGS} ${LDLIBS} `pkg-config --cflags --libs gtk+-2.0`

test: all
	./window

clean:
	-${RM} *~
	-${RM} *.o
	-${RM} *.core
	-${RM} \#*

fclean: clean
	-${RM} client
	-${RM} server