
/*
 * Inspired from nsNightlyToolsService.js and  nsGMNotifier*.js
 */

const AHS_SERVICE_CID = Components.ID("{dd23992c-00f8-4b2f-ac6f-a4dc815f7c2f}");
const AHS_SERVICE_CONTRACTID = "@caspar.regis.free.fr/ahs;1";
const AHS_SERVICE_NAME = "autoHideStatusbar Log Service";
const CC = Components.classes;
const CI = Components.interfaces;
const CR = Components.results;
const HEADER = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'\n" +
               "  'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>\n" +
               "<html>\n<head>\n<meta http-equiv='content-type'" +
               " content='text/html; charset=ISO-8859-1' />\n" +
               "<title>autoHideStatusbar Log</title>\n" +
               "<style type='text/css'>\n" +
               "body{background-color:-moz-dialog;}\n" +
               "div{border:1px solid #000;margin:5px 20px;padding:5px;}\n" +
               "div.p0{background-color:#FFFFFF;text-indent:5em;}\n" +
               "div.p1{background-color:#D9CA82;text-indent:4em;}\n" +
               "div.p2{background-color:#B3A76B;text-indent:3em;}\n" +
               "div.p3{background-color:#B8C5CC;text-indent:2em;}\n" +
               "div.p4{background-color:#F0A77D;text-indent:1em;}\n" +
               "div.p5{background-color:#FFEE99;text-indent:0em;}\n" +
               "</style>\n</head>\n<body>\n";


/**
 * nsAHSLogService constructor
 */
function nsAHSLogService() {
  this.messages = new Array();
}


/**
 * Add a message to the log
 *
 * @param aText: message text
 * @param aPriority: message priority
 */
nsAHSLogService.prototype.addMessage = function(aText, aPriority) {
  this.messages[this.messages.length] = { text: aText, priority: aPriority };
}


/**
 * Clear log messages
 */
nsAHSLogService.prototype.clearMessages = function() {
  this.messages = new Array();
  this.addMessage("Empty Log", 5);
}


/**
 * Return all messages as XHTML code
 */
nsAHSLogService.prototype.getFormattedMessages = function() {
  var result = HEADER;

  for (var i in this.messages)
    result += "<div class='p" + this.messages[i].priority + "'>"
              + this.messages[i].text + "</div>\n";

  return result + "</body>\n</html>";
}


/**
 * Save formated messages into an HTML file
 *
 * @param aParent: parent window of save dialog
 * @param aTitle: title of save dialog
 * @param aFileName: initial filename of save dialog
 */
nsAHSLogService.prototype.saveMessages = function(aParent, aTitle, aFileName) {
  var prop = CC["@mozilla.org/file/directory_service;1"]
             .getService(CI.nsIProperties);
  var file = prop.get("Desk", CI.nsIFile);
  var fp = CC["@mozilla.org/filepicker;1"].createInstance(CI.nsIFilePicker);

  fp.init(aParent, aTitle, fp.modeSave);
  fp.displayDirectory = file;
  fp.defaultString = "autoHideStatusbar-Log.html";
  fp.appendFilters(fp.filterHTML);

  if (fp.show() != fp.returnCancel) {
    var fos = CC["@mozilla.org/network/file-output-stream;1"]
              .createInstance(CI.nsIFileOutputStream);
    var txt = this.getFormattedMessages();

    fos.init(fp.file, 0x02 | 0x08 | 0x20, 0664, 0); // write, create, truncate
    fos.write(txt, txt.length);
    fos.close();

    return true;
  }

  return false;
}



/**
 * JS XPCOM stuffs
 */
nsAHSLogService.prototype.QueryInterface = function(aIID) {
  if (aIID.equals(CI.nsIAHSLogService) || aIID.equals(CI.nsISupports))
    return this;

  throw CR.NS_ERROR_NO_INTERFACE;
}


var AHSLogService = new nsAHSLogService();
var nsAHSLogServiceModule = new Object();


nsAHSLogServiceModule.registerSelf = function(aCompMgr, aFileSpec, aLocation, aType) {
  aCompMgr = aCompMgr.QueryInterface(CI.nsIComponentRegistrar);
  aCompMgr.registerFactoryLocation(AHS_SERVICE_CID, AHS_SERVICE_NAME,
                                   AHS_SERVICE_CONTRACTID, aFileSpec,
                                   aLocation, aType);
}


nsAHSLogServiceModule.unregisterSelf = function(aCompMgr, aFileSpec, aLocation) {
  aCompMgr = aCompMgr.QueryInterface(CI.nsIComponentRegistrar);
  aCompMgr.unregisterFactoryLocation( AHS_SERVICE_CID, aFileSpec );
}


nsAHSLogServiceModule.getClassObject = function(aCompMgr, aCID, aIID) {
  if (!aCID.equals(AHS_SERVICE_CID))
    throw CR.NS_ERROR_NO_INTERFACE;

  if (!aIID.equals(CI.nsIFactory))
    throw CR.NS_ERROR_NOT_IMPLEMENTED;

  return nsAHSLogServiceFactory;
}


nsAHSLogServiceModule.canUnload = function(aCompMgr) {
  AHSLogService.messages = null;
  return true;
}


var nsAHSLogServiceFactory = new Object();


nsAHSLogServiceFactory.createInstance = function(aOuter, aIID) {
  if (aOuter != null)
    throw CR.NS_ERROR_NO_AGGREGATION;

  return AHSLogService.QueryInterface(aIID);
}


function NSGetModule(aCompMgr, aFileSpec) {
  return nsAHSLogServiceModule;
}

