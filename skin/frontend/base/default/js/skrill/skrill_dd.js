cnp_jQuery(document).on('copyandpay:ready', function() {
	
	$(".cnpForm").removeClass("cardStyleSprite");	
	$(".cnpForm").removeClass("style-directDebit");	
	
	
	$(".accountHolderInputField ").removeClass("customInputField");	

	$(".accountHolderInputField ").val($(".cust_name").val());	
	
	$(".countryLabel").hide();
	$(".countrySelect").hide();
	$(".countrySelectBox").hide();
	$(".cardStyleSprite").hide();
	$(".accountHolderLabel").hide();
	$(".accountHolderInput").hide();
	$(".accountNumberLabel").hide();
	$(".accountBankLabel").hide();
	$(".submitInput").hide();
	
	$(".accountNumberInputField ").removeAttr("placeholder");
	$(".accountBankInputField ").removeAttr("placeholder");
	
	$(".cnpForm").prepend('<div class="formbank">');
	// $(".formbank").append('<div class="logobank">');
	// $(".logobank").append($(".linkskrill"));
	// $(".linkskrill").append('<img src="' + $(".img_url").val() + '/skrill.jpg" width="40%">');
	
	$(".formbank").append('<div class="brandImg">');
	$(".brandImg").append('<ul class="listImg">');
	$(".listImg").append('<li><img src="' + $(".img_url").val() + '/elv.gif" alt="ELV"></li>');	

	$(".formbank").append('<div class="banktitle">');
	$(".banktitle").append($(".txtBankTitle"));
	
	$(".formbank").append('<div class="secured">');
	$(".secured").append($(".txtSSL"));
	$(".secured").append('<img src="' + $(".img_url").val() + '/ssl.jpg">');
	
	$(".formbank").append('<div class="itForm">');
	$(".itForm").append('<div class="textInfo">');
	$(".textInfo").append($(".txtAccNumber"));
	$(".itForm").append('<div class="itField">');	
	$(".itField").append($(".accountNumberInputField"));
	
	$(".formbank").append('<div class="itForm itFormBankCode">');
	$(".itFormBankCode").append('<div class="textInfo textInfoBankCode">');
	$(".textInfoBankCode").append($(".txtBankCode"));
	$(".itFormBankCode").append('<div class="itField itFieldBankCode">');	
	$(".itFieldBankCode").append($(".accountBankInputField"));
	
	$(".formbank").append('<div class="itForm itFormBank">');
	$(".itFormBank").append('<div class="textInfo textInfoBank">');
	$(".textInfoBank").append($(".txtBank"));
	$(".itFormBank").append('<div class="itField itFieldBank">');	
	$(".itFieldBank").append('<input class="customInputField" autocomplete="off" name="ACCOUNT.CREDIT">');

	$(".formbank").append('<br><br>');
	
	$(".formbank").append('<div class="sbtForm">');
	$(".sbtForm").append('<div class="btnFrm">');
	$(".btnFrm").append($(".btnCustomCancel"));
	
	$(".sbtForm").append('<div class="btnFrm btnFrmSubmit">');
	$(".btnFrmSubmit").append($(".btnCustomSubmit"));	
	
});
