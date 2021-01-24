(function($) {
    "use strict";

    var success_class = "";

    $("#wpll_settings").on("submit", function() {
        var _ajax_nonce = wpll.nonce_settings;
        var action = "nonce_settings_action";
        var data = $(this).serialize();
        var form_data =
            "_ajax_nonce=" + _ajax_nonce + "&action=" + action + "&" + data;

        $.ajax({
            url: wpll.ajaxurl,
            type: "POST",
            dataType: "json",
            data: form_data,
            beforeSend: function() {
                $("#save_changes").val("Please wait...");
                $("#save_changes").prop("disabled", true);
                $("#ajax_settings_result").html(wpll.saving);
            },
            success: function(result) {
                $("#save_changes").val("Save Changes");
                $("#save_changes").prop("disabled", false);
                if (result.success) {
                    success_class = "updated";
                } else {
                    success_class = "error";
                }
                $("#ajax_settings_result").html(
                    "<div class='" + success_class + "'>" + result.data.message + "</div>"
                );
            },
        });
        return false;
    });

    // =============
    // For Role tab
    // =============

    var $choose_role_type = $('.choose_role_type'),
        $role_list = $('.role_list'),
        $where_to_redirect = $('.where_to_redirect'),
        $redirect_to = $('.redirect_to'),
        $selected_role_type = $choose_role_type.val(),
        $selected_where_to_redirect = $where_to_redirect.val();

    // For existing db values
    $('.' + $choose_role_type.val()).show();
    $('.' + $where_to_redirect.val()).show();

    console.log($where_to_redirect.val());

    // For existing db values end here

    $choose_role_type.on('change', function() {
        var value = $(this).val();
        if (value) {
            value = '.' + $(this).val();
            $role_list.show('slow').not(value).hide();
        }
    });

    $where_to_redirect.on('change', function() {
        var value = $(this).val();
        if (value) {
            var value = '.' + $(this).val();
            $(this).parents('tr').find($redirect_to).show().not(value).hide();
        }
    });

})(jQuery);