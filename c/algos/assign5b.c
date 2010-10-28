#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/time.h>

struct timeval;

int main(int argc, char* argv[]){
	float **main;
	float *temp;
	
	FILE *fp;
	int size, cur;
	char str[256], strb[256];
	
	int scani, i, scanj, j;
	float scanw;
	
	struct timeval t1, t2;
	float time;
	
	if(argc < 2){
		printf("You must pass the target file name on the command line.\n");
		return 1;
	}
	
	fp = fopen(argv[1], "r");
	if(fp == NULL){
		printf("Error opening '%s'.\n", argv[1]);
		return 1;
	}
	
	gettimeofday(&t1, NULL);
	
	//Determine size
	size = 0;
	while(fgets(str, 255, fp)!=NULL) { 
		size++;
	}
	
	rewind(fp);
	
	main = (float**)malloc(sizeof(float*)*size);
	for(i=0;i<size;i++){
		temp = (float*)malloc(sizeof(float)*size);
		main[i] = temp;
		for(j=0;j<size;j++){
			main[i][j] = 100000.0f;
		}
	}
	
	//Read data
	while(fscanf(fp, "%d:%s", &scani, str) != EOF){
		strcat(str, ";"); //padding
		while(sscanf(str, "%d,%f%s", &scanj, &scanw, strb) != -1){
			if (strb[0] == ';')
				for(i=0;strb[i]!=0;i++)
					strb[i] = strb[i+1];
			main[scani-1][scanj-1] = scanw;
			main[scanj-1][scani-1] = scanw;
			strcpy(str, strb);
		}
	}
	
	fclose(fp);
	//Begin with row0,col0
	for(scani=0;scani<size;scani++){
		for(i=0;i<size;i++){
			if(i==scani)
				continue;
			for(j=0;j<size;j++){
				if(j==scani)
					continue;
				scanw = main[scani][j] + main[i][scani];
				if(scanw < main[i][j])
					main[i][j] = scanw;
			}
		}
	}
	
	gettimeofday(&t2, NULL);
	time = ((float)t2.tv_sec + ((float)t2.tv_usec/1000000.0f)) - ((float)t1.tv_sec + ((float)t1.tv_usec/1000000.0f));
	
	//print result
	printf("   ");
	for(i=0;i<size;i++)
		printf("%8d", i+1);
	printf("\n");
	for(i=0;i<size;i++){
		printf("%-3d", i+1);
		for(j=0;j<size;j++){
			printf("%8.2f", main[j][i]);
		}
		printf("\n");
	}
	printf("Runtime: %.5fs\n", time);
	
	
	return 0;
}
