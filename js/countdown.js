function countdown_redirect(timer_element)
{timer_start=timer_element.html();var cTicks=parseInt(timer_start);var timer=setInterval(function()
{if(cTicks)
{timer_element.html(--cTicks);}
else
{clearInterval(timer);}},1000);}

/*
 * doTimeout - v0.4 - 7/15/2009
 * http://benalman.com/projects/jquery-dotimeout-plugin/
 * 
 * Copyright (c) 2009 "Cowboy" Ben Alman
 * Licensed under the MIT license
 * http://benalman.com/about/license/
 */
(function($){var a={},c="doTimeout",d=Array.prototype.slice;$[c]=function(){return b.apply(window,[0].concat(d.call(arguments)))};$.fn[c]=function(){var e=d.call(arguments),f=b.apply(this,[c+e[0]].concat(e));return typeof e[0]==="number"||typeof e[1]==="number"?this:f};function b(l){var m=this,h,k={},n=arguments,i=4,g=n[1],j=n[2],o=n[3];if(typeof g!=="string"){i--;g=l=0;j=n[1];o=n[2]}if(l){h=m.eq(0);h.data(l,k=h.data(l)||{})}else{if(g){k=a[g]||(a[g]={})}}k.id&&clearTimeout(k.id);delete k.id;function f(){if(l){h.removeData(l)}else{if(g){delete a[g]}}}function e(){k.id=setTimeout(function(){k.fn()},j)}if(o){k.fn=function(p){o.apply(m,d.call(n,i))&&!p?e():f()};e()}else{if(k.fn){j===undefined?f():k.fn(j===false);return true}else{f()}}}})(jQuery);