(function($) {

    $.dbj_sound = {
        tracks: {},
        enabled: true,
        
        url : function ( host_element ) {
             var url = $(host_element).attr("href") ;
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

})(jQuery);