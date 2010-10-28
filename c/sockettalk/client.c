#define _CLIENT_
#include "includes.h"
#include "my.h"
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>

int main(int argc, char **argv){
  int sockfd, portnum;
  struct sockaddr_in serv_addr;
  struct hostent *host_name;
  char *msg;

  if(argc<4){
    my_str("Usage: ./client <host_name> <port_number> <message>\n");
    exit(1);
  }

  if((host_name = gethostbyname(argv[1]))==NULL){
    my_str("Invalid hostname\n");
    exit(4);
  }

  portnum = my_atoi(argv[2]);
  if (portnum<1 || portnum>65535){
    my_str("Port must be between 1 and 65535\n");
    exit(2);
  }

  msg = my_vect2str(&argv[3]);

  sockfd = socket(AF_INET, SOCK_STREAM, 0);
  bzero((char*)&serv_addr, sizeof(serv_addr));
  serv_addr.sin_family = AF_INET;
  serv_addr.sin_port = htons(portnum);
  bcopy((char*)host_name->h_addr_list[0], (char*)&serv_addr.sin_addr.s_addr, host_name->h_length);
  /* serv_addr.sin_addr.s_addr = (struct in_addr*)host_name->h_addr_list[0];  */
  if(connect(sockfd, (struct sockaddr*)&serv_addr, sizeof(serv_addr))<0){
    perror("connect");
    exit(3);
  }
  my_str("Connected to server... sending message now.\n");
  while(*msg != '\0'){
    write(sockfd, msg, 1);
    msg++;
  }
  write(sockfd, "\0", 1);
  close(sockfd);
  /*my_str("Message sent; exiting.\n");*/
  usleep(5000);
  return 0;
}
