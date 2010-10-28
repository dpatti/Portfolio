#ifndef _MY_H_
#define _MY_H_

#ifndef NULL
#define NULL 0
#endif

#ifndef TRUE
#define TRUE 1
#endif

#ifndef FALSE
#define FALSE 0
#endif

/*void *malloc(int); 
void free(void*);
void exit(int);
void write(int, void*, int);*/

void my_char(char);
void my_str(char*);
void my_int(int);
void my_num_base(int, char*);
void my_alpha();
void my_digits();
int my_find(char*, char);
int my_rfind(char*, char);
int my_strlen(char*);
int my_revstr(char*);

char *my_strcat(char*, char*);
int my_strcmp(char*, char*);
int my_strncmp(char*, char*, int);
char *my_strconcat(char*, char*);
char *my_strnconcat(char*, char*, int);
char *my_stdstrnconcat(char*, char*, int);
char *my_strcpy(char*, char*);
char *my_strncpy(char*, char*, int);
char *my_strdup(char*);
char *my_strindex(char*, char);
char *my_strrindex(char*, char);
void *xmalloc(int);

char *my_vect2str(char**);
char **my_str2vect(char*);
int my_atoi(char*);
#endif
