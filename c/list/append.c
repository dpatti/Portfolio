#include "../lib/mylist.h"

void append(node *add, node **head){
  if(add == NULL || head == NULL)
    return;
  if(*head == NULL)
    return add_node(add, head);

  node *iter = *head;
  while(iter->next != NULL)
    iter = iter->next;
  iter->next = add;

  return;
}
