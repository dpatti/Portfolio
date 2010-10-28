#include "../lib/mylist.h"

void empty_list(node** head){
  if(head == NULL)
    return;
  
  while(*head != NULL)
    remove_node(head);
}
