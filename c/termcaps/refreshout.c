#include "select.h"

void refreshout(int i){
  if (gl_env.elems[i].mode)
    tputs(gl_env.stout, 1, my_char2);
  my_str(gl_env.elems[i].elem);
  if(gl_env.elems[i].mode)
    tputs(gl_env.stend, 1, my_char2);
}
