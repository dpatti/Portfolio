#include "select.h"

void term_ve(){
  char *str = my_strdup(VE);
  str[0] = ESC;
  tputs(str, 1, my_char2);
}
