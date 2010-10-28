NAME=select
SRC=fill_elems.c init_tty.c main.c my_char2.c refreshout.c refreshin.c restore_tty.c show_params.c term_clear.c term_get_cap.c term_init.c term_pos.c term_ve.c term_vi.c
OBJ=${SRC:.c=.o}
RM=rm -f
LIBPATH=../lib/
CFLAGS=-pedantic -Wall -I${LIBPATH}
LDFLAGS=-L${LIBPATH} -I${LIBPATH}
LDLIBS=-lmy -ltermcap

all: ${OBJ}
	cc -o select ${LDFLAGS} ${OBJ} ${LDLIBS}

test: all
	./select *.c

clean:
	-${RM} *~
	-${RM} *.o
	-${RM} *.core
	-${RM} \#*

fclean: clean
	-${RM} select