// this is where the bma prefs will go
pref("bmakiosk.admin.login", "admin");
pref("bmakiosk.admin.savedinreg", false);
pref("bmakiosk.attract.enabled", false);
pref("bmakiosk.screensaver.enabled", false);
pref("bmakiosk.startup.homepage", "http://www.mozilla.org/");
pref("bmakiosk.attract.page", "http://google.com/");
pref("bmakiosk.aup.enabled", false);
pref("bmakiosk.aup.filepath", "[No file specified]");
pref("bmakiosk.aup.filterpath", "[No file specified]");
pref("bmakiosk.browser.mode", "full");
pref("bmakiosk.cache.diskenabled", false);
pref("bmakiosk.cache.memenabled", false);
pref("bmakiosk.filter.enabled", false);
pref("bmakiosk.filter.filepath", "[No file specified]");
pref("bmakiosk.jsall.enabled", false);
pref("bmakiosk.jsfilter.filepath", "[No file specified]");
pref("bmakiosk.reset.timer", 1);
pref("bmakiosk.reset.timer.on", true);
pref("bmakiosk.reset.warningenabled", false);
pref("bmakiosk.reset.warningtimer", 20);
pref("bmakiosk.save.buttontext", "Save");
pref("bmakiosk.reset.buttontext", "Reset");
pref("bmakiosk.save.location", "");
pref("bmakiosk.save.usefilepicker", false);
pref("bmakiosk.tabs.enabled", true);
pref("bmakiosk.ui.show", true);
pref("bmakiosk.toolbar.currentset", "nav-container,reload-button,stop-button,home-button,urlbar-container,print-button,zoom-control,reset-container,navigator-throbber");
pref("bmakiosk.statusbar", false);
pref("bmakiosk.print.dialog", false);

// protocols
pref("bmakiosk.protocols.file", false);
pref("bmakiosk.protocols.ftp", false);

// Firefox prefs that need to be preset --pete
pref("security.warn_entering_secure", false);
pref("security.warn_leaving_secure", false);
pref("security.warn_submit_insecure", false);

// tabs
pref("browser.tabs.warnOnClose", false);
// pref("browser.tabs.autoHide", true);

// Scripts & Windows prefs
// pref("dom.disable_image_src_set",           true);
pref("dom.disable_window_flip",             true);
pref("dom.disable_window_move_resize",      true);
// pref("dom.disable_window_status_change",    true);

pref("dom.disable_window_open_feature.titlebar",    true);
pref("dom.disable_window_open_feature.close",       true);
pref("dom.disable_window_open_feature.toolbar",     true);
pref("dom.disable_window_open_feature.location",    true);
pref("dom.disable_window_open_feature.directories", true);
pref("dom.disable_window_open_feature.personalbar", true);
pref("dom.disable_window_open_feature.menubar",     true);
pref("dom.disable_window_open_feature.scrollbars",  true);
pref("dom.disable_window_open_feature.resizable",   true);
pref("dom.disable_window_open_feature.minimizable", true);
pref("dom.disable_window_open_feature.status",      true);

pref("dom.allow_scripts_to_close_windows",          true);

pref("dom.disable_open_during_load",                true);

// browser
pref("browser.link.open_newwindow.restriction", 0); 
pref("browser.link.open_newwindow", 3);
pref("browser.link.open_external", 3);
pref("privacy.popups.showBrowserMessage", false);

// xpinstall
// pref("xpinstall.enabled", false);

// app updates
pref("app.update.enabled", false);
pref("app.update.auto", false);

// session store
pref("browser.sessionstore.enabled", false);
pref("browser.formfill.enable", false);
pref("signon.prefillForms", false);
pref("signon.rememberSignons", false);

// DEBUG
pref("bmakiosk.debug.enabled", true);


