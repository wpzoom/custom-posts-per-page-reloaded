jQuery(function ($) {
    $(".cppp-settings").tabs({
        classes: {"ui-tabs-active": "current"},
        active: localStorage.getItem("wpzoomCpppSettingsTab"),
        activate: function (event, ui) {
            localStorage.setItem("wpzoomCpppSettingsTab", $(this).tabs('option', 'active'));
        }
    });
});