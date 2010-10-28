public class BuyerData extends FileParse {
	private class Buyer implements Comparable {
		private String name;
		private int presale;
		private int section;
		private int numTickets;
		private float maxPrice;
		
		public Buyer(String name, int presale, int section, int numTickets, float maxPrice){
			this.name = name;
			this.presale = presale;
			this.section = section;
			this.numTickets = numTickets;
			this.maxPrice = maxPrice;
		}
		
		public int compareTo(Object b){
			if (this.presale < ((Buyer)b).presale)
				return -1;
			if (this.presale > ((Buyer)b).presale)
				return 1;
			return 0;
		}
	}
	
	private MyPriorityQueue buyers;
	
	public BuyerData(){
		super();
		buyers = new MyPriorityQueue();
	}
	
	public BuyerData(String fileSource){
		super(fileSource);
		System.out.println("Buyers loaded successfully.");
	}
	
	public void HandleData(String in, int line){
		if(buyers == null)
			buyers = new MyPriorityQueue();
		String[] parseData = ParseLine(in);
		if (parseData.length != 5) {
			System.out.println("Error: Buyer " + line + " formatted incorrectly. Data was discarded.");
		} else {
			add(parseData[0], Integer.parseInt(parseData[1]), Integer.parseInt(parseData[2]), Integer.parseInt(parseData[3]), Float.parseFloat(parseData[4]));
		}
	}
	
	public void add(String name, int presale, int section, int numTickets, float maxPrice){
		buyers.enqueue(new Buyer(name, presale, section, numTickets, maxPrice));
	}
	
	public void PrintSimple(){
		System.out.println("\nBuyers in memory:");
		int size = buyers.getSize();
		for(int i=0;i<size;i++){
			System.out.println(((Buyer)buyers.dequeue()).name);
		}
		buyers.resetSize(size);
	}
	
	public void BeginSimulation(ShowData sd){
		Buyer temp;
		int[] seatInfo;
		System.out.println("\nBeginning simulation...");
		sd.CleanSections();
		int size = buyers.getSize();
		for(int i=0;i<size;i++){
			temp = ((Buyer)buyers.dequeue());
			sd.FindSeat(temp.name, temp.section, temp.numTickets, temp.maxPrice);
			//break;
		}
		buyers.resetSize(size);
		System.out.println("Simulation complete.");
		
	}
}
