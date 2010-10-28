#ifndef _MYLIST_H_
#define _MYLIST_H_

#include "my.h"

typedef struct s_node {
  void *elem;
  struct s_node *next;
} t_node;
typedef t_node node;

node *new_node(void*, node*);
void add_node(node*, node**);
void add_elem(void*, node**);
void append(node*, node**);
void add_node_at(node*, node**, int);
void remove_node(node**);
void remove_node_at(node**, int);
void remove_last(node**);
int count_nodes(node*);
void empty_list(node**);
node *node_at(node*, int);
void *elem_at(node*, int);
void traverse_int(node*);
void traverse_string(node*);
void traverse_str(node*);
void traverse_char(node*);

#endif
