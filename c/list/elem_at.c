#include "../lib/mylist.h"

void *elem_at(node* head, int pos){
  if(head == NULL)
    return NULL;

  while(head != NULL && pos>0){
    head = head->next;
    pos--;
  }
  return head->elem;
}
