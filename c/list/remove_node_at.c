#include "../lib/mylist.h"

void remove_node_at(node **head, int pos){
  if(head == NULL || *head == NULL)
    return;

  if(pos<=0 || (*head)->next == NULL)
    return remove_node(head);

  node *temp = *head;
  node *toRemove;
  while(temp->next->next != NULL && pos>1){
    temp = temp->next;
    pos--;
  }
  toRemove = temp->next;
  temp->next = toRemove->next;
  toRemove->elem = NULL;
  toRemove->next = NULL;
  free(toRemove);
}
