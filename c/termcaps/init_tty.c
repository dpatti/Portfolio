#include "select.h"

void init_tty(){
  char *name;
  int fd;
  name = ttyname(0);
  fd = open(name, O_WRONLY);
  gl_env.save = dup(1);
  dup2(fd, 1);
}
