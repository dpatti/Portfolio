SRC=new_node.c add_node.c add_elem.c append.c add_node_at.c remove_node.c remove_node_at.c remove_last.c count_nodes.c empty_list.c node_at.c elem_at.c traverse_int.c traverse_string.c traverse_char.c traverse_str.c
NAME=libmylist.a
RM=rm -f
OBJ=${SRC:.c=.o}
LIBPATH=../lib/

all: ${OBJ}
	ar -rc ${NAME} ${OBJ}
	ranlib ${NAME}
	cp ${NAME} ${LIBPATH}

test: clean all ../test/assign3.c
	gcc -pedantic -Wall -L../lib -o "../test/assign3" ../test/assign3.c -lmylist -lmy
	../test/assign3

testtwo: all
	gcc -pedantic -Wall -L../lib -o "../test/assign3-list" ../test/list.c -lmylist -lmy
	../test/assign3-list

clean:
	-${RM} *~
	-${RM} *.o
	-${RM} *.core
	-${RM} \#*
	-${RM} ../lib/*~
	-${RM} ../lib/*.o
	-${RM} ../lib/*.core
	-${RM} ../lib/\#*

fclean: clean
	-${RM} ${NAME}