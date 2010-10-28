#include "../lib/mylist.h"

void add_node(node *add, node **head){
  if(add == NULL || head == NULL)
    return;

  add->next = *head;
  *head = add;
}
