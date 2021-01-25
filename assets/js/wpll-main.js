(function($) {
    "use strict";

    var successClass = "";

    $("#wpll_settings").on("submit", function() {
        var _ajaxNonce = wpll.nonce_settings;
        var action = "nonce_settings_action";
        var data = $(this).serialize();
        var formData =
            "_ajax_nonce=" + _ajaxNonce + "&action=" + action + "&" + data;

        $.ajax({
            url: wpll.ajaxurl,
            type: "POST",
            dataType: "json",
            data: formData,
            beforeSend: function() {
                $("#save_changes").val("Please wait...");
                $("#save_changes").prop("disabled", true);
                $("#ajax_settings_result").html(wpll.saving);
            },
            success: function(result) {
                $("#save_changes").val("Save Changes");
                $("#save_changes").prop("disabled", false);
                if (result.success) {
                    successClass = "updated";
                } else {
                    successClass = "error";
                }
                $("#ajax_settings_result").html(
                    "<div class='" + successClass + "'>" + result.data.message + "</div>"
                );
            },
        });
        return false;
    });

    // Declare variable
    var $chooseRoleType = $('.choose_role_type'),
        $roleList = $('.role_list'),
        $whereToRedirect = $('.where_to_redirect'),
        $redirectTo = $('.redirect_to');

    // For existing db values for any roles
    if ($chooseRoleType.val()) {
        $('.' + $chooseRoleType.val()).show();
    }

    // For existing db values for any multiple rolesroles
    $whereToRedirect.each(function() {
        if ($(this).val()) {
            var $that = $('.' + $(this).val()),
                value = '.' + $(this).val();
            $(this).parents('tr').find($that).show().not(value).hide();
        }
    });

    // On any roles change
    $chooseRoleType.on('change', function() {
        var value = $(this).val();
        if (value) {
            value = '.' + $(this).val();
            $roleList.show('slow').not(value).hide();
        }
    });

    // On multiple roles change
    $whereToRedirect.on('change', function() {
        var value = $(this).val();
        if (value) {
            value = '.' + $(this).val();
            $(this).parents('tr').find($redirectTo).show().not(value).hide();
        }
    });

})(jQuery);