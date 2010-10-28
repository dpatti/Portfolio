#include "../lib/mylist.h"

void add_node_at(node *add, node** head, int pos){
  if(add == NULL || head == NULL)
    return;
  if(pos<=0 || *head == NULL)
    return add_node(add, head);
  
  node *temp = *head;
  while(temp->next != NULL && pos>1){ /*stop one early so we can point to new node*/
    temp = temp->next;
    pos--;
  }
  add->next = temp->next;
  temp->next = add;
}
