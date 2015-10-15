### Summary ###
This open source self-check is a GNU licensed web application written in php. It uses **[John Wohler's SIP2 php class](http://code.google.com/p/php-sip2/)** to communicate with a library's integrated library system (ILS), jquery for ajax calls and other javascript tricks, and mysql for (optional) transaction logging. It was designed to run on the Windows edition of Firefox Portable included in the download, which is set up with addons that start the self-check in fullscreen and trigger sounds on button clicks and is configured to suppress the printer dialog box (Firefox for OSX or Linux could likely be set up in the same way).

For further details please see the installation documentation included with the download or submit an issue.

**[Watch a demo video](http://www.youtube.com/watch?v=cCe6uPDkOTo)**

### RFID ###
My library's iteration of the self-check has recently been updated to support Envisionware's RFID. This may be integrated into a future release. Until then please contact me for more information.



### To do... ###
multi-language support, fine payments,email notices to admin on SIP2 connection failure