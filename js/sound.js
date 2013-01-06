/*
  DBJ-SOUND 1.0.3
  jQuery dbj_sound plugin (no flash, or any other simillar control used)
  
  Loosely inspired on code by Joern Zaefferer 
  (also Jules Gravinese http://www.webveteran.com/ ) 
  
  Copyright (c) 2009 Dusan Jovanovic ( http://dbj.org ) 
  
  Licensed under the MIT license:
    http://www.opensource.org/licenses/mit-license.php
 
 ***********************************************************************************   
  
  API Crash course:
  
  return sound file url from host element
  host must be valid html element with attribute href present
  $.dbj_sound.url( host_element )
 
  play a sound as defined by the href of the host_element
  if looping arg present, then loop
  $.dbj_sound.play( host_element, looping )
 
  play "forever" a sound as defined by the href of the host_element
  $.dbj_sound.loop( host_element )
 
  stop a playback of the sound from the href of the host_element
  $.dbj_sound.stop( host_element )
  
  return true if sounds are on and sound defined by href of the host_element
  is playing
  $.dbj_sound.playing( host_element )
  
  toggle on/off all sounds on the current page, controlled by this plugin
  $.dbj_sound.enabledisable( host_element )
 
 1.0.3 addition
    
    preload sounds into the local cache
    $.dbj_sound.jukebox( src1, src2, ... )
    after this call
    $.dbj_sound.jukebox.list 
    contains url's of files cached
  
 */

(function($) {

    $.dbj_sound = {
        tracks: {},
        enabled: true,
        
        url : function ( host_element ) {
             var url = host_element;
             if ( "undefined" == typeof(url) ) throw new Error(0xFF,"DBJ-SOUND EXCEPTION: host element invalid or missing a valid HREF attribute") ;
             return url ;
         },
        
        loop: function ( host_element ) { this.play (host_element,true) ; },

        play: function(host_element, looping ) {
            //
        var sound_jq = function(src) {

        /* I might introduce this for very old browsers, or ... ?
        if ($.browser.msie)
        return $('<bgsound/>').attr({ src: options.track,
        loop: "infinite", // dbj changed from 1
        autostart: true
        }); */
                //
                return $('<embed />').attr({
                    style: "height:0",
                    loop: ( looping ? "true" : "false" ) ,
                    src: options.track,
                    autostart: "true",
                    hidden: "true"
                });
            }

            // sanity checks
            if (!this.enabled) return;
            if (!host_element) return;

            var options = { track: this.url(host_element) }; 

            if (this.tracks[options.track]) {
                var current = this.tracks[options.track];
                current.remove();
            }

            var element = sound_jq();
                element.appendTo(document.body);
                    this.tracks[options.track] = element;
            return element; // which is jQuery object 
        }

        // DBJ added
        , stop: function(host_element) {
            var url = this.url(host_element);
            if (this.tracks[url]) {
                var current = this.tracks[url];
                // Check when Stop is avaiable, but not on a jQuery object
                if ('undefined' != typeof [0].Stop) current[0].Stop();
                else if ('undefined' != typeof current[0].stop) current[0].stop();
                current.remove();
                this.tracks[url] = null;
            }
        }

        // DBJ added
        , playing: function(host_element) {
            if (!$.dbj_sound.enabled) return false;
            return this.tracks[this.url(host_element)] != null;
        }

        // DBJ added
        , enabledisable: function() {
            this.enabled = !this.enabled;
            if (this.enabled == false)
                for (var j in this.tracks) {
                if (this.tracks[j]) {
                    this.tracks[j].remove();
                    this.tracks[j] = null;
                }
            }
            return this.enabled;
        }

    };

    // 1.0.3 addition
    // preload sounds into the local cache
    $.dbj_sound.cache = function() {
    delete $.dbj_sound.cache.list;
    $.dbj_sound.cache.list = [];
    for (var i = 0; i < arguments.length; i++) {
            $("<embed />").attr("src", arguments[i]);
            $.dbj_sound.cache.list.push(arguments[i]);
        }
    }
    $.dbj_sound.cache.list = []; // length == 0


})(jQuery);