public class ShowData extends FileParse {
	private class Section {
		private String name;
		private int rows;
		private int rowSeats;
		private float seatPrice;
		private Hash occupation;
		
		public Section(String name, int rows, int rowSeats, float seatPrice){
			this.name = name;
			this.rows = rows;
			this.rowSeats = rowSeats;
			this.seatPrice = seatPrice;
			this.occupation = new Hash(rows*rowSeats);
		}
		
		public int FindSeat(String name, int number){
			//first check general
			if(rows == -1)
				if(occupation.size()+number > rowSeats)
					return -2;
				else
					return -1;
			
			//now try hash
			int hash = occupation.insert(name, number, rowSeats);
			if(hash != -1)
				return hash;
			
			//last resort, try force-fill
			hash = occupation.force(number, rowSeats);
			if(hash != -1)
				return hash;
			
			return -2;
		}
	}
		
	private String artist;
	private String venue;
	private int numSections;
	private Section[] sections;
	private String[] dataBuffer;
	
	public ShowData(String fileSource){
		super(fileSource);
		if(dataBuffer == null)
			return;
		artist = dataBuffer[0];
		venue = dataBuffer[1];
		numSections = Integer.parseInt(dataBuffer[2]);
		sections = new Section[numSections];
		int i;
		String[] parseData;
		for(i=0;i<numSections;i++){
			if(dataBuffer[3+i] != null){
				parseData = ParseLine(dataBuffer[3+i]);
				if(parseData.length != 4){
					System.out.println("Error: Section " + (i+1) + " is not formatted correctly. Section data has been discarded.");
				} else {
					sections[i] = new Section(parseData[0], Integer.parseInt(parseData[1]), Integer.parseInt(parseData[2]), Float.parseFloat(parseData[3]));
				}
			}
		}
		System.out.println("Show loaded successfully.");
	}
	
	public void HandleData(String in, int line){
		if(dataBuffer == null)
			dataBuffer = new String[32];
		if(line < 32)
			dataBuffer[line] = in;
	}
	
	public void FindSeat(String name, int section, int number, float maxPrice){
		int seatId;
		System.out.print("\t" + name);
		for(int i=0;i<numSections;i++){
			if((section <= (i+1)) && (sections[i].seatPrice*number <= maxPrice)){
				seatId = sections[i].FindSeat(name, number);
				if (seatId != -2){
					System.out.print(": " + sections[i].name);
					if (seatId != -1){
						System.out.print(" Row " + (seatId/sections[i].rowSeats+1) + " Seat(s) " + (seatId%sections[i].rowSeats+1));
						if (number > 1)
							System.out.print("-" + (seatId%sections[i].rowSeats+number));
					}
					System.out.printf(" ($%.2f)\n", (float)(sections[i].seatPrice*number));
					return;
				}
			}
		}
		System.out.print(": No space available\n");
	}
	
	public void CleanSections(){
		for(int i=0;i<numSections;i++)
			sections[i].occupation = new Hash(sections[i].rows*sections[i].rowSeats);
	}		
}
	
	
	
