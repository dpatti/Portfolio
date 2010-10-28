#include "select.h"

void show_params(){
  int maxsize, i, x, y;
  ioctl(1, TIOCGWINSZ, &(gl_env.win));
  // error check?
  gl_env.flag = 0;
  term_clear();
  /*my_int(gl_env.nbelems);
    my_char('\n');*/
  for (i=0,x=0,y=0,maxsize=0;i<gl_env.nbelems;i++,y++){
    if (y >= gl_env.win.ws_row) {
      y = 0;
      x += maxsize + 2;
      maxsize = 0;
    }
    if (maxsize < gl_env.elems[i].size)
      maxsize = gl_env.elems[i].size;
    if (x+maxsize >= gl_env.win.ws_col){
      term_clear();
      term_pos(0, 0);
      my_str("Please enlarge your terminal.");
      gl_env.flag = TRUE;
      break;
    }
    gl_env.elems[i].x = x;
    gl_env.elems[i].y = y;
    term_pos(x, y);
    refreshout(i);
  }
  if (!gl_env.flag)
    refreshin(); //underlines selected
}
