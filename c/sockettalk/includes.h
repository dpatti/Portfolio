#ifndef _INCLUDES_H_
#define _INCLUDES_H_

void bcopy(void*, void*, int);
void close(int);
void pause();
void kill(int, int);
int getpid();
void usleep(int);
void alarm(int);
void bzero(char*, int);
void perror(char*);
#ifdef _SERVER_
/*server globals*/
int gl_clientPID;
int gl_exit;

#else
/*client globals*/
//char gl_ack;

#endif
#endif
