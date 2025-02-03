/* Custom Login screen scripting */

jQuery(document).ready(function(){
	jQuery(".wp_google_login").insertBefore(jQuery("#loginform"));
	jQuery('<div id="adminlogin"><a href="#">Website admin login</a></div>').insertBefore("#loginform");
	jQuery("#adminlogin a").on("click",function(){ jQuery("#loginform").slideDown(); jQuery("input[type=password]").removeAttr("disabled"); });
	jQuery("#loginform").append(jQuery("#nav"));
});
