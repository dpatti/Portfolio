#include "../lib/mylist.h"

int count_nodes(node *head){
  int i=0;

  while(head != NULL){
    head = head->next;
    i++;
  }
  return i;
}
