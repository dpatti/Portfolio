#include "select.h"

void refreshin(){
  term_pos(gl_env.elems[gl_env.current].x, gl_env.elems[gl_env.current].y);
  tputs(gl_env.under, 1, my_char2);
  refreshout(gl_env.current);
  tputs(gl_env.underout, 1, my_char2);
}
