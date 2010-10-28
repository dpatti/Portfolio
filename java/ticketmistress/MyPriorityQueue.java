public class MyPriorityQueue {
	private MyMaxHeap theQueue;	// change to private later
	
	public MyPriorityQueue() {
		theQueue = new MyMaxHeap();
	}
	
	public MyPriorityQueue(Comparable[] arr, int n){
		theQueue = new MyMaxHeap(arr, n);
	}
	
	public boolean enqueue(Comparable o){
		return theQueue.add(o);
	}
	
	public Object dequeue(){
		return theQueue.removeMax();
	}
	
	public void resetSize(int n){
		theQueue.resetSize(n);
	}
	
	public int getSize(){
		return theQueue.getSize();
	}
}