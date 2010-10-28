RM=rm -f
LIBPATH=../lib/
CFLAGS=-pedantic -Wall -I${LIBPATH} -std=c99
LDFLAGS=-L${LIBPATH} -I${LIBPATH}
LDLIBS=-lmy

all: server! client!

test: all
	-killall -q server
	./server 4289&
	./client localhost 4289 what is going on in here

server!: server

client!: client

clean:
	-killall -q server
	-${RM} *~
	-${RM} *.o
	-${RM} *.core
	-${RM} \#*

fclean: clean
	-${RM} client
	-${RM} server