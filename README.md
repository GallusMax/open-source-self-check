# open-source-self-check
An OpenSource Selfcheck system that is FAST, intuitive and - Free Software! 

This repository offers enhancements against the code from late code.google.com/p/open-source-self-check
(see Wiki for details)

Needed: a Library System with SIP2 connectivity allowing checkout(/in)

The Hardware at the desk:
A PC with a browser, even a ThinClient will do. We use Wyse T50 in some installations.
Minimum: a barcode reader acting as USB HID, can be used for patron cards and items. Barcodes can be discriminated syntactically.

Optional, for bulk checkout/checkin:
A RFID Reader which handles reading and (un)locking, we use an OpenSource Java tool named JkbdRF.jar 

Optional, for RFID patron cards:
An RFID HID device, reading the UID off the local cards, we use programmable readers TWN4 Mifare NFC USB

## Changelog
See Wiki
+ 2026-02 Feature: login with QRCode, Authentication with Shibboleth SSO
+ 2026-01 PHP8-fix: replace calls to each() , which are no longer supported
