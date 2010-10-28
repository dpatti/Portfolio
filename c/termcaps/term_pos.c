#include "select.h"

void term_pos(int x, int y){
  tputs(tgoto(gl_env.pos, x, y), 1, my_char2);
}
