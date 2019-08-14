function checkingAllPaymentEnabled() {
	var flexible_active = document.getElementById("skrill_skrill_flexible_active").value;
	var list_payments = [
		"acc",
		"ali",
		"amx",
		"csi",
		"did",
		"din",
		"dnk",
		"ebt",
		"epy",
		"gcb",
		"gir",
		"glu",
		"idl",
		"jcb",
		"mae",
		"msc",
		"npy",
        "ntl",
		"obt",
		"pli",
		"psc",
		"psp",
		"pwy",
		"sft",
		"vsa",
		"vse",
		"wlt"
	];
	for(i=0; i<list_payments.length;i++){
		if (flexible_active == 1)
		{
			document.getElementById("skrill_skrill_"+ list_payments[i] +"_show_separately").value = 0;
		}
	}
}

function checkingAllCardEnabled() {
	var acc_active = document.getElementById("skrill_skrill_acc_active").value;
	var acc_separately = document.getElementById("skrill_skrill_acc_show_separately").value;
	var list_cards = [
		"vsa",
		"msc",
		"amx",
		"din",
		"jcb"
	];
	for(i=0; i<list_cards.length;i++){
		if (acc_active == 1 && acc_separately == 1)
		{
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_active").disabled=true;
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_active").className = document.getElementById("skrill_skrill_"+ list_cards[i] +"_active").className + " input-disabled";
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_show_separately").disabled=true;
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_show_separately").className = document.getElementById("skrill_skrill_"+ list_cards[i] +"_active").className + " input-disabled";
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_sort_order").disabled=true;
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_sort_order").className = document.getElementById("skrill_skrill_"+ list_cards[i] +"_active").className + " input-disabled";
		}
		else
		{
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_active").disabled=false;
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_active").className = document.getElementById("skrill_skrill_"+ list_cards[i] +"_active").className.replace(/input-disabled/g,"");
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_show_separately").disabled=false;
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_show_separately").className = document.getElementById("skrill_skrill_"+ list_cards[i] +"_active").className.replace(/input-disabled/g,"");
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_sort_order").disabled=false;
			document.getElementById("skrill_skrill_"+ list_cards[i] +"_sort_order").className = document.getElementById("skrill_skrill_"+ list_cards[i] +"_active").className.replace(/input-disabled/g,"");
		}
	}
}

function initElementClass(){
	checkingAllCardEnabled();
}

document.getElementById("skrill_skrill_settings_merchant_account").placeholder = "example@mail.de";

document.getElementById("skrill_skrill_flexible_active").onchange = function(){
	checkingAllPaymentEnabled();
};

document.getElementById("skrill_skrill_acc_active").onchange = function(){
	checkingAllCardEnabled();
};

document.getElementById("skrill_skrill_acc_show_separately").onchange = function(){
	checkingAllCardEnabled();
};

initElementClass();