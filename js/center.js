
jQuery.fn.center=function(params){var options={vertical:true,horizontal:true}
op=jQuery.extend(options,params);return this.each(function(){var $self=jQuery(this);var width=$self.width();var height=$self.height();var paddingTop=parseInt($self.css("padding-top"));var paddingBottom=parseInt($self.css("padding-bottom"));var borderTop=parseInt($self.css("border-top-width"));var borderBottom=parseInt($self.css("border-bottom-width"));var mediaBorder=(borderTop+borderBottom)/2;var mediaPadding=(paddingTop+paddingBottom)/2;var positionType=$self.parent().css("position");var halfWidth=(width/2)*(-1);var halfHeight=((height/2)*(-1))-mediaPadding-mediaBorder;var cssProp={position:'absolute'};if(op.vertical){cssProp.height=height;cssProp.top='50%';cssProp.marginTop=halfHeight;}
if(op.horizontal){cssProp.width=width;cssProp.left='50%';cssProp.marginLeft=halfWidth;}
if(positionType=='static'){$self.parent().css("position","relative");}
$self.css(cssProp);});};