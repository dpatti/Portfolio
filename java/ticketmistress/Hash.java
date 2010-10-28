public class Hash {
	private boolean[] arr;
	private int size;
	private int capacity;	
	
	public Hash(int n){
		arr = new boolean[Math.abs(n)];
		size = 0;
		capacity = Math.abs(n);
	}
	
	public int size(){
		return size;
	}
	
	public int insert(String key, int num, int seats){
		int home = h(key)%capacity;
		if(check(home, num, seats))
			return home;
		else
			for(int i=0;i<capacity/2;i++)
				if(check((home+p(key, i))%capacity, num, seats))
					return (home+p(key, i))%capacity;
		return -1;
	}
	
	public int force(int num, int seats){
		int i, j;
		for(i=0;i<capacity;i++)
			if(check(i, num, seats))
				return i;
		return -1;
	}
		
	private boolean check(int pos, int num, int seats){
	
		/*System.out.printf("===checking %d %d %d \n", pos, num, seats);
		System.out.println((arr[pos]));
		System.out.println((pos+num > capacity));
		System.out.println((pos/seats != (pos+num)/seats));*/
		if ((arr[pos]) || (pos+num > capacity) || (pos/seats != (pos+num)/seats))
			return false;
		for(int i=pos;i<pos+num;i++)
			if(arr[i])
				return false;
		for(int i=pos;i<pos+num;i++){
			arr[i] = true;
			size++;
		}
		return true;
	}			
	
	private int h(String k){
		int hash = 0;
		for(int i=0;i<k.length();i++){
			hash += (k.charAt(i) & 0x0F) * (i + 1);
			hash += (k.charAt(i) & 0xF0) * (k.length() - i);
		}
		return hash;
	}
	
	private int p(String k, int i){
		int probe = 0;
		for(int j=0;j<k.length();j++)
			probe += k.charAt(j);
		return probe*i;
	}
}