#ifndef _SELECT_H_
#define _SELECT_H_

#include <sys/ioctl.h>
#include <stdio.h>
#include <fcntl.h>
#include <termios.h>
#include <sys/termios.h>
#include <signal.h>
#include <unistd.h>
#include <stdlib.h>
#include <termcap.h>
#include "my.h"

#define VTI 1
#define KU "\E[A"
#define KD "\E[B"
#define KL "\E[D"
#define KR "\E[C"
#define ESC 27
#define CL "cl"
#define CM "cm"
#define SO "so"
#define SE "se"
#define US "us"
#define UE "ue"
#define VE "\E[?25h"
#define VI "\E[?25l"

typedef struct s_elem{
  char *elem;
  int size;
  int x;
  int y;
  int mode;
} t_elem;

typedef struct s_env {
  char *left;
  char *right;
  char *under;
  char *underout;
  char *up;
  char *down;
  char *esc;
  char *clear;
  char *pos;
  char *stout;
  char *stend;
  struct winsize win;
  struct termio line;
  int flag;
  int save;
  t_elem *elems;
  int nbelems;
  int current;
} t_env;

t_env gl_env;

int my_char2(int);
void show_params();
void init_tty();
void fill_elems(int, char**);
void term_init();
void term_vi();
void term_ve();
void term_clear();
void term_pos();
void refreshout(int);
void refreshin();
void restore_tty();

#endif
