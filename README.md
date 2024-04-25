External Authentication against a local ldap

The index.php answeres a Http Basic Auth with a json response.

Depending on the given user name the authentication is redirected to one of several ldap backends.

Only if the ldap bind is accepted and a useable patron code can be read from the ldap, the patron code is sent back as "accepted".

Any other case ist answered as "no found", allowing for further authentication.

Installation

config_local.php

Sensitive Parameters are included from a local config file.
Anything that could ease an attack is seen as sensitive
* ldap addresses
* ldap Attribute Names
* ldap Search Bases
  
