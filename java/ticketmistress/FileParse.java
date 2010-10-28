import java.io.*;

public abstract class FileParse {
	private boolean fileSuccess;
	
	public FileParse(){
	}
	
	public FileParse(String filename) {
		FileInputStream fin;
		DataInputStream din;
		int line = 0;
		fileSuccess = true;
		try {
			fin = new FileInputStream(filename);
			din = new DataInputStream(fin);
			
			while(din.available() != 0){
				HandleData(din.readLine(), line++);
			}
			
			fin.close();
			din.close();
		} catch (FileNotFoundException e) {
			System.out.println("Error: File not found. No data was read.");
			fileSuccess = false;
			return;
		} catch (IOException e){
			System.out.println("Unknown Error. No data was read.");
			fileSuccess = false;
			return;
		}
	}
	
	public abstract void HandleData(String in, int line);
	
	public String[] ParseLine(String in){
		return in.split("\\|");
	}
	
	public boolean success() {
		return fileSuccess;
	}
}