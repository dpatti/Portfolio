#include "select.h"

void term_vi(){
  char *str = my_strdup(VI);
  str[0] = ESC;
  tputs(str, 1, my_char2);
}
