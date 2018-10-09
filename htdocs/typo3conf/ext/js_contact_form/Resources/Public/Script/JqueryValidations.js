/*
 *  (c) 2014 Jainish Senjaliya <jainish.online@gmail.com>
 *  All rights reserved 


$(document).ready(function() {
	
	function checkValidEmail(field) {
		var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		if (!filter.test(field.val())) {
			return false;
		}
		return true;
	}
	
	function isUrl(s) {
	    var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
	    return regexp.test(s);
	}
	
	function validatePhone(field) {
		   
	    var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
	    
	    if (!filter.test(field.val())) {
	        return false;
	    }
	    return true;
	}

	function len(value) {
		return value.val().length;
	}
	
	function trim(field){
		return jQuery.trim(field.val());	
	}
	
	$('#newContactForm').submit(function(event){
		
		var cValids = 0;
 			
		$("#newContactForm .validate").removeClass("error");
		
		$("#newContactForm .validate").each(function(){
			if($(this).val()==''){
				$($(this)).addClass("error");
				$(this).attr("placeholder",$(this).attr('mendatory_message'));
				cValids = 1;
			} 
		});
		
		if($('#email.validate').length > 0){
			
			var email		= $('#email');
			if(email.val()!=""){
				if(!checkValidEmail(email)) {
					email.attr("placeholder",email.attr('valid_message'));
					$('#email').val("").addClass("error");
					$('#email').effect( "shake",{times:1}, 300 );
					cValids = 1;
				}
			}
		}
		
		if($('#url.validate').length > 0){

			var url		= $('#url');
			if(url.val()!=""){
				if(!isUrl(url.val())) {
					url.attr("placeholder",url.attr('valid_message'));
					$('#url').val("").addClass("error");
					cValids = 1;
				}
			}
		}
		
		if($('#phone.validate').length > 0){
			
			var phone		= $('#phone');
			if(phone.val()!=""){
				if(!validatePhone(phone)) {
					phone.attr("placeholder",phone.attr('valid_message'));
					$('#phone').val("").addClass("error");
					cValids = 1;
				}
			}
		}
		
		if($('#zip.validate').length > 0){
			
			var zip		= $('#zip');
			if(zip.val()!=""){
				if(isNaN(zip.val()) || zip.val().length>6 || zip.val().length<4) {
					zip.attr("placeholder",zip.attr('valid_message'));
					$('#zip').val("").addClass("error");
					cValids = 1;
				}
			}
		}

		
		if(cValids == 0) { 
			$(".formLoading").css("display","block");
		
		}else {
			$(".error").effect( "shake",{times:1}, 600 );
			alert($("#newContactForm .alert").val());
			return false;
		}

	});
	
	
	$(".validate").blur(function(){
		
		if(trim($(this))!=""){
			$(this).removeClass("error");
		}else{
			$(this).addClass("error");
			$(this).attr("placeholder",$(this).attr('mendatory_message'));
			$(this).val("");
			$(this).effect( "shake",{times:1}, 300 );
		}
	})
	
	$(".validate").keyup(function(){
		
		if(trim($(this))!=""){
			$(this).removeClass("error");
		}else{
			$(this).addClass("error");
		}
	})
	
	$("#email.validate").blur(function(){
		
		var email		= $('#email');
		if(email.val()!=""){
			if(!checkValidEmail(email)) {
				alert(email.attr('valid_message'));
				email.attr("placeholder",email.attr('valid_message'));
				$('#email').addClass("error").val("").focus();
				$('#email').effect( "shake",{times:1}, 300 );
			}
		}
	})
	
	$("#zip.validate").blur(function(){
		
		var zip		= $('#zip');
		if(zip.val()!=""){
			if(isNaN(zip.val()) || zip.val().length>6 || zip.val().length<4) {
				alert(zip.attr('valid_message'));
				zip.attr("placeholder",zip.attr('valid_message'));
				$('#zip').addClass("error").val("").focus();
				$('#zip').effect( "shake",{times:1}, 300 );
			}
		}
	})
	
	$("#url.validate").blur(function(){
		
		var url		= $('#url');
		if(url.val()!=""){
			if(!isUrl(url.val())) {
				alert(url.attr('valid_message'));
				url.attr("placeholder",url.attr('valid_message'));
				$('#url').addClass("error").val("").focus();
				$('#url').effect( "shake",{times:1}, 300 );
			}
		}
	})
	
	$("#phone.validate").blur(function(){
		
		var phone		= $('#phone');
		if(phone.val()!=""){
			if(!validatePhone(phone)) {
				alert(phone.attr('valid_message'));
				phone.attr("placeholder",phone.attr('valid_message'));
				$('#phone').addClass("error").val("").focus();
				$('#phone').effect( "shake",{times:1}, 300 );
			}
		}
	})
	
	$(".successMessage").delay(9000).hide(500);
	
});

    */
