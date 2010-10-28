#include "../lib/mylist.h"

void remove_last(node **head){
  if(head == NULL || *head == NULL)
    return;

  if((*head)->next == NULL)
    return remove_node(head);

  node *temp = *head;
  while(temp->next->next != NULL)
    temp = temp->next;
  temp->next->elem = NULL;
  temp->next->next = NULL;
  free(temp->next);
  temp->next = NULL;
}
