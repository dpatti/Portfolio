#include "../lib/mylist.h"

void add_elem(void *elem, node **head){
  add_node(new_node(elem, NULL), head);
}
