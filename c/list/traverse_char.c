#include "../lib/mylist.h"

void traverse_char(node *head){
  if(head == NULL)
    return my_str("NULL\n");

  while(head != NULL){
    my_char(*((char*)head->elem));
    my_char(' ');
    head = head->next;
  }
  my_char('\n');
  return;
}
