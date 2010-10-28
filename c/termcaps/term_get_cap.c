#include "select.h"

char* term_get_cap(char* cap, char **area){
  char *ptr;
  if(!(ptr = tgetstr(cap, area))){
    my_str("Could not get cap '");
    my_str(*cap);
    my_str("'\n");
    exit(1);
  }
  return ptr;
}
