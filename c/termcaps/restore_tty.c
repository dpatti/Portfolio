#include "select.h"

void restore_tty(){
  dup2(gl_env.save, 1);
}
