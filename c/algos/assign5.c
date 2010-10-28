#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/time.h>

int arr_remove(int* arr, int n, int* length){
	int ret = arr[n];
	while(arr[n+1] != -1)
		arr[n] = arr[(n++)+1];
	(*length)--;
	return ret;
}

int* arr_insert(int* arr, int val, int* length, int* max){
	arr[(*length)++] = val;
	if(*length == *max){
		//reallocate more memory
		*max *= 2;
		arr = (int*)realloc(arr, sizeof(int)*(*max));
		for(int i=*length;i<*max;i++)
			arr[i] = -1;
	}
	return arr;
}

int main(int argc, char* argv[]){
	float **main;
	float *distance;
	int *neighbors;
	int nLength=0, nMax;
	
	FILE *fp;
	int size, cur;
	char str[256], strb[256];
	
	int scani, i, scanj, j;
	float scanw;
	
	char **key;
	float total;
	int accounted=1;
	
	struct timeval t1, t2, t3;
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
	while(fgets(str, 256, fp)!=NULL) { 
		size++;
	}
	
	rewind(fp);
	
	main = (float**)malloc(sizeof(float*)*size);
	key = (char**)malloc(sizeof(char*)*size);
	distance = (float*)malloc(sizeof(float)*size);
	for(i=0;i<size;i++){
		key[i] = (char*)malloc(sizeof(char)*size);
		main[i] = (float*)malloc(sizeof(float)*size);
		for(j=0;j<size;j++){
			main[i][j] = 0.0f;
			key[i][j] = 0;
		}
		distance[i] = -1.0f;
	}
	
	neighbors = (int*)malloc(sizeof(int)*64);
	for(i=0;i<64;i++)
		neighbors[i] = -1;
	nMax = 64;
	
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
	
	gettimeofday(&t3, NULL);
	
	//Now begin with node [0]
	//printf("Beginning\n");
	distance[0] = 0.00f;
	for(i=0;i<size;i++){
		if(main[0][i] > 0.0f){
			distance[i] = main[0][i];
			//printf("Inserting %d\n", i);
			neighbors = arr_insert(neighbors, i, &nLength, &nMax);
		}
	}
	//return 0; // temp
	while(accounted<size){
	//printf("Iterating\n");
	//printf("\tSearching\n");
		scani = -1;
		scanw = 100000.0f;
		for(i=0;i<nLength;i++){
			//printf("Considering: %d(%d)=%.2f vs %.2f\n", i, neighbors[i], distance[neighbors[i]], scanw);
			if(distance[neighbors[i]] < scanw){
				scanw = distance[neighbors[i]];
				scani = i;
			}
		}
	//printf("\tRemoving from neighbors\n");
		scani = arr_remove(neighbors, scani, &nLength);
		distance[scani] = 0.00f;
	//printf("\tFinding partner\n");
		for(i=0;i<size;i++){
			if(main[scani][i] == scanw){
				key[scani][i] = 1;
				key[i][scani] = 1;
				break;
			}
		}
	//printf("\tAdding neighbors\n");
		for(i=0;i<size;i++){
			if(main[scani][i] > 0.0f && distance[i] != 0.00f){
				if(distance[i] == -1.00f){
					//printf("Inserting %d(%.2f) with %d, %d\n", i, distance[i], nLength, nMax);
					neighbors = arr_insert(neighbors, i, &nLength, &nMax);
					distance[i] = main[scani][i];
				} else if(distance[i] > main[scani][i])
					distance[i] = main[scani][i];
			}
		}
		total += scanw;
		accounted++;
		//break;
	}
	
	gettimeofday(&t2, NULL);
	time = ((float)t2.tv_sec + ((float)t2.tv_usec/1000000.0f)) - ((float)t3.tv_sec + ((float)t3.tv_usec/1000000.0f));
	
	//print result
	for(i=0;i<size;i++){
		printf("%d:", i+1);
		for(j=0;j<size;j++){
			if(key[i][j]){
				printf("%d,%.2f;", j+1, main[i][j]);
			}
		}
		printf("\n");
	}
	printf("\nFinal weight = %.2f\n", total);
	printf("Runtime: %.5fs\n", time);
	
	return 0;
}
