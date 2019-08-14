cnp_jQuery(document).on('copyandpay:ready', function() {
	
	$(".cnpForm").removeClass("cardStyleSprite");	
	$(".cnpForm").removeClass("style-card");	
	$(".cardNumberInputField").removeClass("customInputField");
	$(".cvvInputField").removeClass("customInputField");

	$(".cardHolderInputField").val($(".cust_name").val());	
	$(".brandSelect").hide();
	$(".brandSelectBox").hide();
	$(".cardNumberInput").hide();
	$(".expiryMonthSelect").hide();
	$(".expiryYearSelect").hide();
	$(".cardHolderInput").hide();
	$(".cardHolderLabel").hide();
	$(".cardIconSprite").hide();
	$(".cardNumberLabel").hide();
	$(".expiryMonthLabel").hide();
	$(".cvvInput").hide();
	$(".cvvLabel").hide();
	$(".submitInput").hide();
	$(".cardSubmitButton").hide();
	
	$(".cardNumberInputField").removeAttr("placeholder");
	$(".cardHolderInputField").removeAttr("placeholder");
	$(".cvvInputField").removeAttr("placeholder");
	
	$(".cnpForm").prepend('<div class="formbank">');
	// $(".formbank").append('<div class="logobank">');
	// $(".logobank").append($(".linkskrill"));
	// $(".linkskrill").append('<img src="' + $(".img_url").val() + '/skrill.jpg" width="40%">');
	$(".formbank").append('<div class="brandImg">');
	$(".brandImg").append('<ul class="listImg">');

	var cardList = $(".card_list").val();

	if( cardList.indexOf("MASTER") > -1 )
	{
		$(".listImg").append('<li><img src="' + $(".img_url").val() + '/mastercard.jpg" class="img_MASTER" alt="MASTERCARD"></li>');
	}
	if( cardList.indexOf("VISA") > -1 )
	{
		$(".listImg").append('<li><img src="' + $(".img_url").val() + '/visa.png" class="img_VISA" alt="VISA"></li>');
	}
	if( cardList.indexOf("MAESTRO") > -1 )
	{
		$(".listImg").append('<li><img src="' + $(".img_url").val() + '/maestro.png" class="img_MAESTRO" alt="MAESTRO"></li>');
	}
	if( cardList.indexOf("VISA") > -1 )
	{
		$(".listImg").append('<li><img src="' + $(".img_url").val() + '/visa-pay.png" class="img_VPAY" alt="V-PAY"></li>');
	}
	if( cardList.indexOf("AMEX") > -1 )
	{
		$(".listImg").append('<li><img src="' + $(".img_url").val() + '/amex.jpg" class="img_AMEX" alt="AMEX"></li>');
	}	

	$(".formbank").append('<div class="secured">');
	$(".secured").append($(".txtSSL"));
	$(".secured").append('<img src="' + $(".img_url").val() + '/ssl.jpg">');

	$(".formbank").append('<div class="itForm">');
	$(".itForm").append('<div class="textInfo">');
	$(".textInfo").append($(".txtCardNumber"));
	$(".itForm").append('<div class="itField">');	
	$(".itField").append($(".cardNumberInputField"));
	
	$(".formbank").append('<div class="itForm itFormDate">');
	$(".itFormDate").append('<div class="textInfo textInfoDate">');
	$(".textInfoDate").append($(".txtExpires"));
	$(".itFormDate").append('<div class="itField itFieldDate">');	
	$(".itFieldDate").append($(".expiryMonthSelectBox"));
	$(".itFieldDate").append($(".expiryYearSelectBox"));
	
	$(".formbank").append('<div class="itForm itFormCvv">');
	$(".itFormCvv").append('<div class="textInfo textInfoCvv">');
	$(".textInfoCvv").append($(".txtCvc"));
	$(".itFormCvv").append('<div class="itField itFieldCvv">');	
	$(".itFieldCvv").append($(".cvvInputField"));
	$(".itFieldCvv").append('<a href="" class="hilfe"></a>');
	$(".hilfe").append($(".txtHelp"));
	
	$(".formbank").append('<br><br>');
	
	$(".formbank").append('<div class="sbtForm">');
	$(".sbtForm").append('<div class="btnFrm">');
	$(".btnFrm").append($(".btnCustomCancel"));
	
	$(".sbtForm").append('<div class="btnFrm btnFrmVer">');
	$(".btnFrmVer").append('<center><img src="' + $(".img_url").val() + '/verivied.jpg" alt="" style="height:30px; z-index:10;"></center>');	
	$(".sbtForm").append('<div class="btnFrm btnFrmSubmit">');
	$(".btnFrmSubmit").append($(".btnCustomSubmit"));	
	
	$(".formbank").append('<div class="overlay">');
	$(".overlay").append('<div class="box-overlay">');
	$(".box-overlay").append('<a style="text-decoration:none;" href="#" class="close-button">x</a>');
	// $(".box-overlay").append('<div class="logobankoverlay">');
	// $(".logobankoverlay").append($(".linkskrilloverlay"));
	// $(".linkskrilloverlay").append('<img src="' + $(".img_url").val() + '/skrill.jpg" width="40%">');
	$(".box-overlay").append('<table class="tbloverlay">');	
	$(".tbloverlay").append('<tr class="trwhatcvc">');	
	$(".trwhatcvc").append('<td class="tdWhatCvc" colspan="2" align="left">');
	$(".tdWhatCvc").append($(".txtWhatCvc"));
	$(".tbloverlay").append('<tr class="trcards">');	
	$(".trcards").append('<td class="tdCards" width="70%" align="justify">');
	$(".tdCards").append($(".txtCards"));	
	$(".tdCards").append($(".txtThreeDigits"));	
	
	$(".trcards").append('<td class="imgthreedigits" style="text-align: right;">');
	$(".imgthreedigits").append('<img src="' + $(".img_url").val() + '/kpn1.png">');
	$(".tbloverlay").append('<tr class="trblank">');
	$(".trblank").append('<td>&nbsp;</td>');
	
		
	$(".cardNumberInputField").keyup(function(){
		checkCard();
	});  
	
	$(".hilfe").click(function(e){
		e.preventDefault();
		$(".overlay").fadeIn();
	});
	$(".close-button").click(function(e){
		e.preventDefault();
		$(".overlay").fadeOut();
	});

	
});

function resetCard()
{
	$(".img_AMEX").addClass('blurBrand');
	$(".img_VISA").addClass('blurBrand');
	$(".img_VPAY").addClass('blurBrand');
	$(".img_MASTER").addClass('blurBrand');
	$(".img_MAESTRO").addClass('blurBrand');
}
function changeCard(cardname)
{
	if (cardname=="MASTERMAESTRO")
	{
		cardnamenow = 'MASTER';
	}
	else
	{
		cardnamenow = cardname;
	}
	
	$(".brandSelectBox").val(cardnamenow);
	
	resetCard();
	var classNow = 'img_' + cardnamenow;			
	$("."+classNow).removeClass('blurBrand');
	if (cardname=="VISA")
	{
		$(".img_VPAY").removeClass('blurBrand');
	}
	if (cardname=="MASTERMAESTRO")
	{
		$(".img_MAESTRO").removeClass('blurBrand');
	}			
}
function checkCard()
{
	var num = $('.cardNumberInputField').val();
	num1 = parseInt(num.substr(0,1));
	num2 = parseInt(num.substr(0,2));
	num6 = parseInt(num.substr(0,6));
	if (num2==34 || num2==37)
	{
		changeCard('AMEX');
	}
	else if (num1==4)
	{
		changeCard('VISA');
	}
	else if (num1==5 && num2<7)
	{
		changeCard('MASTERMAESTRO');
	}	
	else if (num2>=51 && num2<56)
	{
		changeCard('MASTER');
	}
	else if ( (num1==6) || (num2>=50 && num2<51) || (num2>=56 && num2<60) || (num2>=61 && num2<64) || (num2>=66 && num2<70) || (num6>=560000 && num6<=699999) )
	{
		changeCard('MAESTRO');

		if ( (num6>=601100 && num6<=601109) || (num6>=601120 && num6<=601149) || (num6>=601174 && num6<=601174) || (num6>=601177 && num6<=601179) || (num6>=601186 && num6<=601199) || (num6>=644000 && num6<=649999) || (num6>=650000 && num6<=659999) )
		{
			changeCard('EMPTY');
		} 
	}
	else
	{
		changeCard('EMPTY');
	}	
}