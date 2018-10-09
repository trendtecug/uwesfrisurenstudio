$( document ).ready(function() {
        var closeForm = function () {

            TweenLite.to($(".successMessage"), 0.5 , {display:none, onComplete: window.app.contact.close});
        }

        $("#newContactForm").submit(function (event) {
            event.preventDefault();
            var $action = this.action;
            var cValids = 0;

            var checkValidEmail = function (field) {
                var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if (!filter.test(field.val())) {
                    return false;
                }
                return true;
            }

            var isUrl = function (s) {
                var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
                return regexp.test(s);
            }

            var validatePhone = function (field) {

                var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;

                if (!filter.test(field.val())) {
                    return false;
                }
                return true;
            }

            var len = function (value) {
                return value.val().length;
            }

            var trim = function (field) {
                return jQuery.trim(field.val());
            }

            $("#newContactForm .validate").removeClass("error");

            $("#newContactForm .validate").each(function () {
                if ($(this).val() == '') {
                    $($(this)).addClass("error");
                    $(this).attr("placeholder", $(this).attr('mendatory_message'));
                    cValids = 1;
                } else {
                    $($(this)).removeClass("error");
                }
            });

            if ($('#email.validate').length > 0) {

                var email = $('#email');
                if (email.val() != "") {
                    if (!checkValidEmail(email)) {
                        email.attr("placeholder", email.attr('valid_message'));
                        $('#email').val("").addClass("error");
                        $('#email').effect("shake", {times: 1}, 300);
                        cValids = 1;
                    } else {
                        $('#email').removeClass("error");
                    }
                }
            }

            if ($('#url.validate').length > 0) {

                var url = $('#url');
                if (url.val() != "") {
                    if (!isUrl(url.val())) {
                        url.attr("placeholder", url.attr('valid_message'));
                        $('#url').val("").addClass("error");
                        cValids = 1;
                    } else {
                        $('#url').removeClass("error");
                    }
                }
            }

            if ($('#phone.validate').length > 0) {

                var phone = $('#phone');
                if (phone.val() != "") {
                    if (!validatePhone(phone)) {
                        phone.attr("placeholder", phone.attr('valid_message'));
                        $('#phone').val("").addClass("error");
                        cValids = 1;
                    } else {
                        $('#phone').removeClass("error");
                    }
                }
            }

            if ($('#zip.validate').length > 0) {

                var zip = $('#zip');
                if (zip.val() != "") {
                    if (isNaN(zip.val()) || zip.val().length > 6 || zip.val().length < 4) {
                        zip.attr("placeholder", zip.attr('valid_message'));
                        $('#zip').val("").addClass("error");
                        cValids = 1;
                    } else {
                        $('#zip').removeClass("error");
                    }
                }
            }


            if (cValids == 0) {
                // Formular abschicken...
                /*            var $firstName = $('#firstname').val();
                 var $lastname = $('#lastname').val();
                 var $email = $('#email').val();
                 var $phone = $('#phone').val();
                 var $enquiry = $('#enquiry').val();
                 var $message = $('#message').val();
                 var tx_jscontactform_contactform = [];
                 tx_jscontactform_contactform['firstname'] = $firstName;
                 tx_jscontactform_contactform['lastname'] = $lastname;
                 tx_jscontactform_contactform['email'] = $email;
                 tx_jscontactform_contactform['phone'] = $phone;
                 tx_jscontactform_contactform['enquiry'] = $enquiry;
                 tx_jscontactform_contactform['message'] = $message;
                 tx_jscontactform_contactform['__referrer'] = [];

                 $('input[name^="pages_title"]').each(function() {

                 }
                 tx_jscontactform_contactform['__referrer']['@extension'] = $('input[name=tx_jscontactform_contactform[__referrer][@extension]]');
                 tx_jscontactform_contactform['__referrer']['@extension'] = $('input[name=tx_jscontactform_contactform[__referrer][@vendor]]');
                 tx_jscontactform_contactform['__referrer']['@extension'] = $('input[name=tx_jscontactform_contactform[__referrer][@controller]]');
                 tx_jscontactform_contactform['__referrer']['@extension'] = $('input[name=tx_jscontactform_contactform[__referrer][@action]]');
                 tx_jscontactform_contactform['__referrer']['@extension'] = $('input[name=tx_jscontactform_contactform[__referrer][arguments]]');
                 tx_jscontactform_contactform['__trustedProperties'] = $('input[name=tx_jscontactform_contactform[__trustedProperties]]');
                 */

                //var $data = { tx_jscontactform_contactform : tx_jscontactform_contactform};
                var $data = $('[name^="tx_jscontactform_contactform"]');
                // $data.push( $('textarea[name^="tx_jscontactform_contactform"]'));
                $.ajax({
                    type: "POST",
                    url: $action,
                    data: $data,
                    statusCode: {
                        302: function () {
                        }
                    }
                }).done(function (msg) {
                    $(".formLoading").css("display", "none");
                    $(".successMessage").show();
                    TweenLite.to($(".successMessage"), 0.5 , {display:block});
                    $('#firstname').val('');
                    $('#lastname').val('');
                    $('#email').val('');
                    $('#phone').val('');
                    $('#enquiry').val('Anfrage 1');
                    $('#message').val('');
                    TweenLite.delayedCall(4, closeForm);

                });
            }

        });

});