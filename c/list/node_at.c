#include "../lib/mylist.h"

node *node_at(node* head, int pos){
  if(head == NULL)
    return NULL;

  while(head != NULL && pos>0){
    head = head->next;
    pos--;
  }
  return head;
}
