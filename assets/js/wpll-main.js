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

    // var role_type = $(".choose_role_type").val();
    // if (role_type.length > 0) {
    //     // If select data is found
    //     $(".general_table_heading").show();
    //     if (role_type == 1) {
    //         $(".any_roles").show("slow");
    //     } else if (role_type == 2) {
    //         $(".multiple_roles").show("slow");
    //     }
    //     // For select change
    //     $(".choose_role_type").change(function() {
    //         var role_type = $(this).val();
    //         if (role_type == 1) {
    //             $(".any_roles").show("slow");
    //             $(".multiple_roles").hide("slow");
    //         } else if (role_type == 2) {
    //             $(".any_roles").hide("slow");
    //             $(".multiple_roles").show("slow");
    //         }
    //     });
    // }

    var $choose_role_type = $('.choose_role_type'),
        $role_list = $('.role_list'),
        $where_to_redirect = $('.where_to_redirect'),
        $redirect_to = $('.redirect_to');

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