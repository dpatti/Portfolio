#include "select.h"

void term_init(char *bp, char *area){
  char *term;
  gl_env.esc = (char*)xmalloc(2*sizeof(char));
  gl_env.esc[0] = ESC;
  gl_env.esc[1] = '\0';
  
  if(!(term = (char*)getenv("TERM"))){
    my_str("Could not access the terminal environment\n");
    exit(1);
  }
  if(!tgetent(bp, term)){
    my_str("tgetent failed\n");
    exit(1);
  }
  gl_env.clear = term_get_cap(CL, &area);
  gl_env.up = term_get_cap("ku", &area);
  gl_env.down = term_get_cap("kd", &area);
  gl_env.left = term_get_cap("kl", &area);
  gl_env.right = term_get_cap("kr", &area);
  gl_env.under = term_get_cap(US, &area);
  gl_env.underout = term_get_cap(UE, &area);
  gl_env.stout = term_get_cap(SO, &area);
  gl_env.stend = term_get_cap(SE, &area);
  gl_env.pos = term_get_cap(CM, &area);
}
