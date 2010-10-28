public class MyMaxHeap {
	private Object[] theHeap;
	private int size;
	private int capacity;
	
	static final int START_SIZE = 64;
	
	public MyMaxHeap(){
		theHeap = new Object[START_SIZE];
		capacity = START_SIZE;
		size = 0;
	}
	
	public MyMaxHeap(Comparable[] arr, int n){
		int i;
		theHeap = new Object[START_SIZE];
		if((arr == null) || (n<1) || (arr.length != n)) {
			size = 0;
		} else {	
			for(i=0;i<n;i++){
				theHeap[i] = arr[i];
			}
			size = n;
			buildHeap();
		}
	}
	
	public void resetSize(int n){
		size = n;
	}
	
	public int getSize(){
		return size;
	}
	
	public boolean add(Comparable o){
		if (o == null)// || (size >= MAX_SIZE))
			return false;
		//Check for resize
		if (size == capacity){
			capacity *= 2;
			Object[] temp = theHeap;
			theHeap = new Object[capacity];
			for(int i=0;i<size;i++)
				theHeap[i] = temp[i];
		}
		//Insert into end and push up
		int pos = size;
		theHeap[size++] = o;
		while(parent(pos) >= 0 && ((Comparable)theHeap[pos]).compareTo(theHeap[parent(pos)]) > 0){
			swap(pos, parent(pos));
			pos = parent(pos);
		}
		return true;		
	}
	
	public Object removeMax(){
		if(size < 1)
			return null;
		swap(0, --size);
		shiftDown(0);
		return theHeap[size];
	}
		
	private void swap(int m, int n){
		Object temp = theHeap[m];
		theHeap[m] = theHeap[n];
		theHeap[n] = temp;
	}
	
	private void shiftDown(int index){	
		int max, lc, rc;
		lc = leftChild(index);
		//System.out.println(index);
		if((lc >= size) || (theHeap[index] == null))
			return;
		rc = lc + 1;
		//System.out.println("--"+size+" "+lc+" "+rc);
		//Set lc to the biggest child
		if((rc < size) && (((Comparable)theHeap[lc]).compareTo(theHeap[rc]) < 0))
			lc = rc;
		//Swap if we need to and recurse
		if(((Comparable)theHeap[index]).compareTo(theHeap[lc]) < 0){
			swap(index, lc);
			shiftDown(lc);
		}
	}
	
	private void buildHeap(){
		int i;
		for(i=size-1;i>=0;i--){
			shiftDown(i);
		}
	}
	
	private int leftChild(int index){
		return 2*index+1;
	}
	
	private int rightChild(int index){
		return 2*index+2;
	}
	
	private int parent(int index){
		return (index-1)/2;
	}
	
	public void parseInts(){
		if ((size>0) && (theHeap[0] instanceof Integer)) {
			int i, j, n=1;
			for(i=0;i<size;i=j,n*=2){
				for(j=i;j<Math.min(i+n,size);j++)
					System.out.print(theHeap[j].toString() + " ");
				System.out.println("");
			}
		}
	}
	
	public void parseStrings(){
		if ((size>0) && (theHeap[0] instanceof String)) {
			int i;
			for(i=0;i<size;i++)
				System.out.print(theHeap[i] + " ");		
		}
	}
}