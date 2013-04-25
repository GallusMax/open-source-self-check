<?php
/**
* ldap query Class
*
* This class provides preconfigured ldap query 
*
* PHP version 5
*
*
* @package    
* @author     Ulrich Hahn
* @licence    http://opensource.org/licenses/gpl-3.0.html
* @copyright  Ulrich Hahn
* @version    $Id$
* @link       
*/

/**
*  
*  TODO
*   - extract configuration
*/

/**
* General Usage:
*    include('ldap.php');
*
*    // create object
*    $myldap = new ldap;
*
*    // Set host name
*    $myldap->hostname = 'server.example.com';
*    $myldap->port = 631;
*    
*
*    // set search base
*    $myldap->binddn = 'cn=...';
*	 $myldap->bindpw = 'password';
*    $myldap->searchbase = 'ou=...';
*    
*    // connect to server
*    $result = $myldap->connect();
*
* 	// Search for "$filter=pattern"
*   $myldap->search('pattern');
*    // Get certain attributes
*    $cn = $myldap->getattr('cn');
*
*    
*/

class ldap {

    /* Public variables for configuration */
    public $hostname	= "ads1.library.hsu-hh.de";
    public $port         = 389; /* default sip2 port for Sirsi */
    public $binddn 		= 'cn=hitagreader,ou=Technical,ou=HSU HH,dc=library,dc=hsu-hh,dc=de';
    public $bindpw 		= 'hitagpass';
    public $searchbase	= "ou=Library Users,ou=HSU HH,dc=library,dc=hsu-hh,dc=de";
    public $filter		=	'carLicense';
	
	private $lc=null; // the connection
    private $sres=null; // search result
    
        function connect() {

        /* Socket Communications  */
//        $this->_debugmsg( "ldap: --- BEGIN LDAP communication ---");  
       
		$this->lc=ldap_connect($this->hostname,$this->port);

		if(!$this->lc){
//            $this->_debugmsg("ldap: connect() failed.\n");
            return false;
        } else {
//            $this->_debugmsg( "ldap: --- READY ---" );
        } 
        
        if(ldap_bind($this->lc,$this->binddn,$this->bindpw)){
        	return true;
        }else{
        	return false;
        }
        
    }  
    
    function search($string){
    	if(!$this->lc) return false;
		$this->sres=ldap_search($this->lc,$this->searchbase,$this->filter."=".$string);
    
    }
    
    function getattr($attr){
    	if(!$this->sres) return false;
		$entry=ldap_first_entry($this->lc,$this->sres);
		$cnread[0]='nothing found';
		if($entry){
			$cnread=ldap_get_values($this->lc,$entry,$attr);
			return $cnread[0];
		}
		return false;
	}
    
    function getcnfromuid($uid){
   	if(!$this->lc)$this->connect();
    	 
    if(!$this->lc) return false;

    $this->search($uid);
    return $this->getattr('cn');
    }
    
    
}
    
