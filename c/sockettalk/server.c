#define _SERVER_
#include "includes.h"
#include "my.h"
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <signal.h>

void interrupt();

int main(int argc, char **argv){
  int sockfd, newsockfd, portnum;
  socklen_t clilen;
  char buffer[256];
  struct sockaddr_in serv_addr, cli_addr;
  
  /*check args*/
  if(argc<2){
    my_str("Usage: ./server <port_number>\n");
    exit(1);
  }
  
  portnum = my_atoi(argv[1]);
  if (portnum<1 || portnum>65535){
    my_str("Port must be between 1 and 65535\n");
    exit(2);
  }

  signal(SIGINT, interrupt);
  sockfd = socket(AF_INET, SOCK_STREAM, 0);
  bzero((char*)&serv_addr, sizeof(serv_addr));
  serv_addr.sin_family = AF_INET;
  serv_addr.sin_port = htons(portnum);
  serv_addr.sin_addr.s_addr = INADDR_ANY;
  if(bind(sockfd, (struct sockaddr*)&serv_addr, sizeof(serv_addr))<0){
     perror("bind");
     exit(5);
  }
  listen(sockfd, 5);
  clilen = sizeof(cli_addr);
  while(1){
    if((newsockfd=accept(sockfd, (struct sockaddr*)&cli_addr, &clilen))<0){
      perror("accept");
      exit(4);
    }
    usleep(2000);
    my_str("Server received: ");
    while(read(newsockfd, &buffer, 256) > 0)
      my_str(buffer);
    my_str("\nServer: Message end. Waiting for next connection.\n");
  }

  return 0;
}

void interrupt(){
  my_str("Server exiting.\n");
  exit(0);
}
