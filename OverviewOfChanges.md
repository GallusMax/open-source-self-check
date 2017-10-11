Several functional extensions were applied during the past years. Here comes a short list which will be documented in more detail separately.

### Checkin allowed 
An additional button opens the checkout screen with checkin functionality. Patron login is not necessary.

### RFID integration 
A RFID driver "glue" monitors items in range of the antenna and manages the tag reading / locking / unlocking as triggered by the browser. 

### bulk checkin /checkout with RFID 
A stack of items can be placed on the antenna and will be processed one after the other.
The RFID "glue" can handle up to 16 tags simultaneously. A typical reading range within a stack of books is 6. 

### RFID patron cards / ldap integration
We use another type of small reader which just read the (immutable) UID from the patron card. A lookup in two ldap systems returns the patron number.

### registration of RFID patron cards (not covered in master branch)##
Initialization of unknown RFID patron cards is done with another screen.

### optional event advertise popup after account closing (not covered in master branch)##
In order to promote institutional events we sometimes offer an advertsing popup after logout. The popup can contain any html content and will fade out after a minute.
