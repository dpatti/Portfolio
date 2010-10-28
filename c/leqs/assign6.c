#include <stdio.h>
#include <stdlib.h>
#include <string.h>

struct pair {
	int index;
	int zeroes;
};

int comparison(const struct pair *a, const struct pair *b){
	int temp = a->zeroes - b->zeroes;
	if (temp > 0)
		return 1;
	else if (temp < 0)
		return -1;
	return 0;
}

int main(int argc, char* argv[]){
	float **coeffs;
	struct pair *sorted;
	
	FILE *fp;
	int size;
	char str[1024], strb[1024];
	
	int i, j, k;
	float scanv;
			
	if(argc < 2){
		printf("You must pass the target file name on the command line.\n");
		return 1;
	}
	
	fp = fopen(argv[1], "r");
	if(fp == NULL){
		printf("Error opening '%s'.\n", argv[1]);
		return 1;
	}
		
	//Determine size
	size = 0;
	while(fgets(str, 1024, fp) != NULL) { 
		size++;
	}
	
	rewind(fp);
	
	coeffs = (float**)malloc(sizeof(float*)*size);
	sorted = (struct pair*)malloc(sizeof(struct pair)*size);
	for(i=0;i<size+1;i++){
		coeffs[i] = (float*)malloc(sizeof(float)*size);
	}
	
	printf("Starting scan.\n");
	i=0;
	while(fscanf(fp, "%s", str) != EOF){
		j=0;
		strcat(str, ","); //padding
		while(sscanf(str, "%f%s", &scanv, strb) != -1){
			//printf("%d: (%s)%f(%s)\n", j, str, scanv, strb);
			if (strb[0] == ',')
				for(k=0;strb[k]!=0;k++)
					strb[k] = strb[k+1];
			if(j>size){
				printf("File error: Entry must be a square matrix. (line %d)\n", i);
				return -1;
			} else {
				coeffs[i][j] = scanv;
			}
			strcpy(str, strb);
			j++;
		}
		if(j != size+1){
			printf("File error: Entry must be a square matrix. (%d != %d)\n", j, size+1);
			return -1;
		}
		i++;
	}
	fclose(fp);
				/*for(t=0;t<size;t++){
					printf("%d: ", t);
					for(r=0;r<size+1;r++){
						printf("%6.2f", coeffs[t][r]);
					}
					printf("\n");
				}	*/	
	printf("Starting sort.\n");
	for(i=0;i<size;i++){
		for(j=0;(j<size)&&(coeffs[i][j]==0.0f);j++);
		if(j == size){
			printf("File error: Unexpected all zero row.\n");
			return -1;
		}
		sorted[i].index = i;
		sorted[i].zeroes = j;
	}
	qsort(sorted, size, sizeof(struct pair), comparison);
	
	float pivot, rhead;
	printf("Starting elimination.\n");
	for(j=0;j<size;j++){
		//for each column, choose the same index row as your pivot
		pivot = coeffs[sorted[j].index][j];
		//printf("  pivot=%f\n", pivot);
		for(i=0;i<size;i++){
			if(i != j){
				//found a row we must eliminate
				rhead = coeffs[sorted[i].index][j];
				//printf("  rhead=%f(%d)\n", rhead, sorted[i].index);
				for(k=j;k<size+1;k++){
					coeffs[sorted[i].index][k] = coeffs[sorted[i].index][k] - (coeffs[sorted[j].index][k] * rhead / pivot);
				}
			}
		}
		
		//now reduce the current row
		//printf("REDUCING %f\n", pivot);
		for(i=sorted[j].zeroes;i<size+1;i++){
			coeffs[sorted[j].index][i] /= pivot;
		}
		/* 
		for(t=0;t<size;t++){
			printf("%d(%d): ", sorted[t].index, sorted[t].zeroes);
			for(r=0;r<size+1;r++){
				printf("%6.2f", coeffs[sorted[t].index][r]);
			}
			printf("\n");
		}	
		printf("\n");
		 */
	}
	
	printf("Solutions to variables in order:\n");
	for(i=0;i<size;i++){
		printf("%6.3f\n", coeffs[sorted[i].index][size]);
	}
	
	return 0;
}
