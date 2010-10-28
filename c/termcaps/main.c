#include "select.h"

int main(int argc, char **argv){
  int n, i;
  char c[4], bp[1024], area[2048], print=FALSE;
  struct termio line;
  
  if (argc < 2) {
    my_str("Usage: ./select <file(s)>\n");
    return 1;
  }

  init_tty();
  fill_elems(argc, argv);
  signal(SIGWINCH, show_params);
  term_vi();
  term_init(bp, area);
  ioctl(0, TCGETA, &line);
  gl_env.line = line;
  line.c_lflag &= ~(ECHO | ISIG | ICANON);
  line.c_cc[VMIN] = 3;
  line.c_cc[VTIME] = VTI;
  ioctl(0, TCSETA, &line);
  show_params();

  while(1){
    n = read(0, c, 3);
    c[n] = '\0';

    /*my_str("\n");
    for(i=0;i<n;i++){
      my_int(c[i]);
      my_char('\n');
    }
    my_str("\n");*/
    if (my_strcmp(c, gl_env.esc) == 0) {
      // esc
      break;
    } else if (my_strcmp(c, "\n") == 0) {
      // enter
      print = TRUE;
      break;
    } else if (my_strcmp(c, " ") == 0) {
      // space
      gl_env.elems[gl_env.current].mode = 1 - gl_env.elems[gl_env.current].mode;
      if (gl_env.elems[gl_env.current].mode) {
	gl_env.current = gl_env.current + 1;
	if (gl_env.current >= gl_env.nbelems)
	  gl_env.current = 0;
      }
      show_params();
    } else if (my_strcmp(c, KU) == 0) {
      // up
      gl_env.current -= 1;
      if (gl_env.current < 0)
	gl_env.current = gl_env.nbelems - 1;
      show_params();
    } else if (my_strcmp(c, KD) == 0) {
      // down
      gl_env.current += 1;
      if (gl_env.current >= gl_env.nbelems)
	gl_env.current = 0;
      show_params();
    } else if (my_strcmp(c, KL) == 0) {
      // left
      gl_env.current -= gl_env.win.ws_row;
      if (gl_env.current < 0)
	gl_env.current = 0;
      show_params();
    } else if (my_strcmp(c, KR) == 0) {
      //right
      gl_env.current += gl_env.win.ws_row;
      if (gl_env.current >= gl_env.nbelems)
	gl_env.current = gl_env.nbelems-1;
      show_params();
    }
  }
  term_clear();
  ioctl(0, TCSETA, &(gl_env.line));
  term_ve();
  restore_tty();
  
  if (print) {
    //term_clear();
    //term_pos(0, 0);
    for (i=0;i<gl_env.nbelems;i++){
      if (gl_env.elems[i].mode) {
	my_str(gl_env.elems[i].elem);
	my_char(' ');
      }
    }
  }

  return 0;
}
