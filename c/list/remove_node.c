#include "../lib/mylist.h"

void remove_node(node **head){
  if(head == NULL || *head == NULL)
    return;

  node *temp = *head;
  *head = (*head)->next;
 
  temp->elem = NULL;
  temp->next = NULL;
  free(temp);
 }

