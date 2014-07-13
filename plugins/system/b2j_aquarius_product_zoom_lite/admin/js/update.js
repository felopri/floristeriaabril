jQuery(document).ready(function(){
    if(jQuery("#b2j-update-container").length > 0)
    {
        jQuery("#b2j-update-container").appendTo(".plg-desc");
        jQuery(".plg-desc").append("<div class='clr'></div>");
        jQuery("#b2j-update-container").fadeIn(500);
    }

    if(jQuery("#b2j-description").length > 0)
    {
        jQuery("#b2j-description").appendTo(".plg-desc");
        jQuery("#b2j-description").fadeIn(500);
    }
});