#include "select.h"

void fill_elems(int c, char **v){
  int i;

  gl_env.elems = (t_elem*)xmalloc(c*sizeof(t_elem));
  for (i=1;i<c;i++) {
    gl_env.elems[i-1].elem = v[i];
    gl_env.elems[i-1].size = my_strlen(v[i]);
    /*my_str(gl_env.elems[i-1].elem);
      my_char('\n');*/
  }
  gl_env.nbelems = c-1;
  gl_env.current = 0;
}
