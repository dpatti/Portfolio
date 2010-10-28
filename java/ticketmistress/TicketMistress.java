import java.util.Scanner;

class TicketMistress {	
	public static void main(String[] args){
		ShowData show;
		BuyerData buyers;
		int response;
		Scanner in = new Scanner(System.in);
		//For manual entry
		String buyerName;
		int buyerPresale;
		int buyerSection;
		int buyerTickets;
		float buyerPrice;
		
		System.out.println("Ticket Mistress initializing...");
		System.out.println("Loading showSetup.dat...");
		show = new ShowData("showSetup.dat");
		if (!show.success())
			show = null;
		buyers = null;
		
		response = 0;
		do {
			System.out.println("\nEnter a number to select an action:");
			System.out.println("1. Setup new show (will delete current show)");
			System.out.println("2. Load buyers file");
			System.out.println("3. Add buyer manually");
			System.out.println("4. View buyers list");
			System.out.println("5. Start simulation");
			System.out.println("6. Exit");
			System.out.print("> ");
			while(!in.hasNextInt()){
				System.out.println("Please enter a number 1-6.");
				System.out.print("> ");
				in.nextLine();
			}
			response = in.nextInt();
						
			if((response < 1) || (response > 6))
				continue;
				
			//Clear line
			in.nextLine();
			
			switch(response){
				case 1:
					System.out.print("Enter show filename: ");
					show = new ShowData(in.nextLine());
					if (!show.success())
						show = null;
					break;
				case 2:
					System.out.print("Enter buyers filename: ");
					buyers = new BuyerData(in.nextLine());
					if (!buyers.success())
						buyers = null;
					break;
				case 3:
					if(buyers == null)
						buyers = new BuyerData();
					System.out.print("Enter buyer name: ");
					buyerName = in.nextLine();
					System.out.print("Enter presale level: ");
					buyerPresale = in.nextInt();
					System.out.print("Enter preferred section number: ");
					buyerSection = in.nextInt();
					System.out.print("Enter desired tickets: ");
					buyerTickets = in.nextInt();
					System.out.print("Enter maximum price: ");
					buyerPrice = in.nextFloat();
					buyers.add(buyerName, buyerPresale, buyerSection, buyerTickets, buyerPrice);
					break;
				case 4:
					if(buyers == null)
						System.out.println("No buyers in memory.");
					else
						buyers.PrintSimple();
					break;
				case 5:
					if((buyers == null) || (show == null))
						System.out.println("You must set up the show and/or buyers before simulating.");
					else
						buyers.BeginSimulation(show);
					break;					
			}
			
		} while(response != 6);
		
		in.close();
	}
}