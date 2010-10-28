 public class Driver {
	public static void main(String[] args){
		/*Integer[] a = {24, 75, 16, 94, 45, 05, 89, 23, 24, 61, 07, 94, 79, 57, 64};//{0,1,2,3,4,5,6,7,8,9,10,11,12,13,14};
		//String[] a = {"Bob", "Fred", "Chuck", "Ellie", "Sarah", "Casie", "Lester", "Haha wow"};
		MyPriorityQueue pq = new MyPriorityQueue(a, a.length);
		int i;
		for(i=0;i<5;i++){
			//pq.enqueue(new Integer(i));
			//System.out.println(pq.dequeue());
		}
		pq.theQueue.parseInts();
		//pq.theQueue.parseStrings();*/
		MyPriorityQueue pq = new MyPriorityQueue();
		pq.enqueue(new Integer(5));
		pq.enqueue(new Integer(7));
		pq.enqueue(new Integer(4));
		pq.enqueue(new Integer(6));
		pq.enqueue(new Integer(2));
		
		while(pq.getSize() > 0)
			System.out.println(pq.dequeue());
	}
}