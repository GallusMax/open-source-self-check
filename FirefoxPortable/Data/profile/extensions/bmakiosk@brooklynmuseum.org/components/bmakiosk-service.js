const DEBUG                 = 0;

const nsISupports           = Components.interfaces.nsISupports;
const nsICommandLineHandler = Components.interfaces.nsICommandLineHandler;
const nsIFactory            = Components.interfaces.nsIFactory;
const nsIWindowMediator     = Components.interfaces.nsIWindowMediator;
const nsIWindowWatcher      = Components.interfaces.nsIWindowWatcher;
const nsISupportsString     = Components.interfaces.nsISupportsString;
const nsIPrefService        = Components.interfaces.nsIPrefService;


var gPrefService = Components.classes["@mozilla.org/preferences-service;1"].getService(nsIPrefService);
var gPref        = gPrefService.getBranch("bmakiosk.");

function debug ()
{
  if (DEBUG) dump(Array.join(arguments, ": ") + "\n");
}

function setHomePage (aURI)
{
  debug("SETTING", "setHomePage", aURI.spec);
try
{
  gPref.setCharPref("startup.homepage", aURI.spec);
}
  catch (e) { debug(e); }
}

function setSplashPage (aURI)
{
  debug("SETTING", "setSplashPage", aURI.spec);
  gPref.setCharPref("attract.page", aURI.spec);
}

function handleArgs (aArg)
{
  // debug("arg <"+aArg+ ">");

  var ww = Components.classes['@mozilla.org/embedcomp/window-watcher;1']
                     .getService(nsIWindowWatcher);

  var args = Components.classes['@mozilla.org/supports-string;1']
                       .getService(nsISupportsString);

  switch (aArg)
  {
    case "admin":
      ww.openWindow(null, "chrome://bmakiosk/content/settings.xul", "_blank", 
                    "chrome,dialog=no,resizable=no,centerscreen", args);
      break;

    case "about":
      ww.openWindow(null, "chrome://bmakiosk/content/aboutDialog.xul", "_blank", 
                    "chrome,dialog,dependent=no,resize=no,centerscreen", args);
      break;

    case "title":
      args.data = new Array("title");
      ww.openWindow(null, "chrome://bmakiosk/content/", "_blank", 
                    "all,chrome,dialog=no", args);
      break;

    default:
      ww.openWindow(null, "chrome://bmakiosk/content/", "_blank", 
                    "all,chrome,dialog=no", args);
  }

  return 0;
}

function goQuitApp ()
{
  var appStartup = Components.classes['@mozilla.org/toolkit/app-startup;1'].
                   getService(Components.interfaces.nsIAppStartup);

  appStartup.quit(Components.interfaces.nsIAppStartup.eAttemptQuit);
}

var nsBMACommandLineHandler = 
{
  QueryInterface : function clh_QI (iid) 
  {
    if (!iid.equals(nsISupports) &&
        !iid.equals(nsICommandLineHandler) &&
        !iid.equals(nsIFactory))
      throw Components.results.NS_ERROR_NO_INTERFACE;

    return this;
  },

  handle : function clh_handle (cmdLine) 
  {
    var shouldQuit = false;
    var uri = null;

    var flag = cmdLine.findFlag("kiosk", false);

    var len = cmdLine.length;

    for (var i=0; i<len; ++i)
    {
      if (/^-/.test(cmdLine.getArgument(i)))
      {
        var flg = cmdLine.getArgument(i).replace(/^-/, "").toLowerCase();
        var page = null;

        if (flg == "sethomepage") 
        {
          shouldQuit = true;
          page = cmdLine.getArgument(i+1);
          if (!/^-/.test(page))
          {
            uri = cmdLine.resolveURI(page);
            setHomePage(uri);
            continue;
          }
        }

        if (flg == "setsplashpage") 
        {
          shouldQuit = true;
          page = cmdLine.getArgument(i+1);
          if (!/^-/.test(page))
          {
            uri = cmdLine.resolveURI(page);
            setSplashPage(uri);
            continue;
          }
        }
      }
    }

    var arg;

    i = cmdLine.findFlag("setHomePage", false);

    if (i >= 0) 
    {
      arg = cmdLine.getArgument(i+1);
      cmdLine.removeArguments(i, arg ? i+1 : i);
    }

    i = cmdLine.findFlag("setSplashPage", false);

    if (i >= 0) 
    {
      arg = cmdLine.getArgument(i+1);
      cmdLine.removeArguments(i, arg ? i+1 : i);
    }

    if (flag < 0 && shouldQuit)
    {
      goQuitApp();
      return;
    }

    if (flag < 0) return;

    try
    {
      arg = cmdLine.getArgument(++flag);
    }
      catch (e) { arg = null; }

    if (!arg) arg = "default";

    if (/^-/.test(arg))
    {
      dump("Warning: unrecognized command line flag [" +arg+ "]\n");
      arg = "default";
      return;
    }

    cmdLine.preventDefault = true;

    handleArgs(arg);
  },

  helpInfo : "Usage: firefox -kiosk          \n" 
           + "Usage: firefox -kiosk admin    \n",

  createInstance: function clh_CI (outer, iid) 
  {
    if (outer != null)
      throw Components.results.NS_ERROR_NO_AGGREGATION;

    return this.QueryInterface(iid);
  },

  lockFactory : function clh_lock (lock) { }
}

const clh_contractID = "@mozilla.org/bma/clh;1";
const clh_CID = Components.ID("{601ac075-ab89-41c1-a732-a835dd1c7442}");

var Module = 
{
  QueryInterface : function QI (iid) 
  {
    if (iid.equals(Components.interfaces.nsIModule) &&
        iid.equals(Components.interfaces.nsISupports))
      return this;

    throw Components.results.NS_ERROR_NO_INTERFACE;
  },

  getClassObject : function (compMgr, cid, iid) 
  {
    if (cid.equals(clh_CID))
      return nsBMACommandLineHandler.QueryInterface(iid);

    throw Components.results.NS_ERROR_FAILURE;
  },
    
  registerSelf : function mod_regself (compMgr, fileSpec, location, type) 
  {
    var compReg =
      compMgr.QueryInterface(Components.interfaces.nsIComponentRegistrar);

    compReg.registerFactoryLocation(clh_CID,
                                    "nsBMACommandLineHandler",
                                    clh_contractID,
                                    fileSpec,
                                    location,
                                    type);

    var catMan = Components.classes["@mozilla.org/categorymanager;1"]
                           .getService(Components.interfaces.nsICategoryManager);


    catMan.addCategoryEntry("command-line-handler",
                            "m-bma",
                            clh_contractID, true, true);
  },

  unregisterSelf : function mod_unregself (compMgr, location, type) 
  {
    var compReg = 
      compMgr.QueryInterface(Components.interfaces.nsIComponentRegistrar);

    var catMan = Components.classes["@mozilla.org/categorymanager;1"]
                           .getService(Components.interfaces.nsICategoryManager);

    catMan.deleteCategoryEntry("command-line-handler", "y-bma", true);
  },

  canUnload : function(compMgr) { return true; }
}

function NSGetModule (compMgr, fileSpec) { return Module; }

