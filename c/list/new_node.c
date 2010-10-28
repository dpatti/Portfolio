#include "../lib/mylist.h"

node *new_node(void* elem, node* next){
  node *temp = (node*)xmalloc(sizeof(node));
  temp->elem = elem;
  temp->next = next;

  return temp;
}
