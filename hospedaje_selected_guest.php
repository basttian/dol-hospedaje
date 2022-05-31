<?php
/**
 * Copyright (C) 2018    Andreu Bisquerra    <jove@bisquerra.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/takepos/invoice.php
 *	\ingroup    takepos
 *	\brief      Page to generate section with list of lines
 */

// if (! defined('NOREQUIREUSER'))    define('NOREQUIREUSER', '1');    // Not disabled cause need to load personalized language
// if (! defined('NOREQUIREDB'))        define('NOREQUIREDB', '1');        // Not disabled cause need to load personalized language
// if (! defined('NOREQUIRESOC'))        define('NOREQUIRESOC', '1');
// if (! defined('NOREQUIRETRAN'))        define('NOREQUIRETRAN', '1');


// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include "../main.inc.php";
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");
require_once DOL_DOCUMENT_ROOT .'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

$action = GETPOST('action', 'aZ09');
$place = (GETPOST('place', 'aZ09') ? GETPOST('place', 'aZ09') : 0);
$now = dol_now();
$date_start = GETPOST('date_start', 'alpha');
$date_end = GETPOST('date_end', 'alpha');
$persons = GETPOST('persons', 'ing');
$guest = GETPOST('guest', 'ing');
$productid = GETPOST('productid', 'ing');

$idline = GETPOST('idline', 'int');
$number = GETPOST('number', 'alpha');
$qty = GETPOST('number', 'int');

/*
 * CONSOLE LOG ELIMINAR AL FINAL
 */
function console_log( $data ){
    echo '<script>';
    echo 'console.log('.json_encode( $data ).')';
    echo '</script>';
}

// Security check
if (! $user->rights->hospedaje->hospedaje->posbutton) {
    accessforbidden();
}

/*
 * Actions
 */

$placeid = 0; //ID of invoice
$invoiceid = GETPOST('invoiceid', 'int');


$invoice = new Facture($db);
if ($invoiceid > 0) {
    $ret = $invoice->fetch($invoiceid);
} else {
    $ret = $invoice->fetch('', '(PROV-POS'.$_SESSION["takeposterminal"].'-'.$place.')');
}
if ($ret > 0) {
    $placeid = $invoice->id;
}


if (!empty($persons)) {

    $head='<meta name="apple-mobile-web-app-title" content="TakePOS"/>
    <meta charset=UTF-8>
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>';
    top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss);
    
    /*
     * Actions
     */
    
    ?>
	<script type="text/javascript" src="../../takepos/js/jquery.colorbox-min.js"></script>
	<script>

	$( document ).ready(function() {
		
		var xhttp = new XMLHttpRequest();
			xhttp.open("POST", "<?php echo DOL_URL_ROOT; ?>/takepos/invoice.php", false);
			xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                	<?php 
                	if ( !empty($idline)) {
                	require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
                	require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
                	require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
                	$invoice = new Facture($db);
                	$invoice->fetch('', '(PROV-POS'.$_SESSION["takeposterminal"].'-'.$place.')'); 
                	$customer = new Societe($db);
                	$customer->fetch($invoice->socid);
                	foreach($invoice->lines as $row){
                	    if ($idline == $row->id) {
                	        $prod = new Product($db);
                	        $prod->fetch( $row->fk_product);
                	        $datapriceofproduct = $prod->getSellPrice($mysoc, $customer, 0);
                	        $productid = $datapriceofproduct['pu_ttc'];
                	?>
    					var descp = "<?php echo dol_escape_js($langs->transnoentities('Guest')); ?>";
    					parent.$("#poslines").load("<?php echo DOL_URL_ROOT; ?>/custom/hospedaje/hospedaje_actions.php?place=<?php echo $place; ?>&invoiceid=<?php echo $placeid; ?>&action=dayrow&desc="+descp+"&number=<?php echo $productid ;?>&qty=<?php echo $persons; ?>&descguest=<?php echo $guest ;?>", function() {
                            parent.$("#poslines").load("invoice.php?place=<?php echo $place; ?>&invoiceid=<?php echo $placeid ;?> ", function() {
                                parent.$.colorbox.close();
       	                    });
    					});		
            		<?php
                	    }
                	}
                    $invoice->fetch($placeid);
                    $invoice->update($user);
                	}else{
            		?>
                		parent.$.colorbox.close();
                    	alert("<?php echo $langs->trans('aviso2');?>");	
                    <?php 
                	}
                    ?>
                }
              }
    		xhttp.send(" place=<?php echo $place;?>&invoiceid=<?php echo $placeid;?> ");
	});
	</script>
	<?php
	exit;
}


/*
 * View
 */
 
 // Title
$title='TakePOS - Dolibarr '.DOL_VERSION;
if (! empty($conf->global->MAIN_APPLICATION_TITLE)) $title='TakePOS - '.$conf->global->MAIN_APPLICATION_TITLE;
$head='<meta name="apple-mobile-web-app-title" content="TakePOS"/>
<meta charset=UTF-8>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<link rel="stylesheet" href="../../takepos/css/pos.css.php">';
top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss);
$arrayofcss = array('../../takepos/css/pos.css.php');
$arrayofjs  = array();



?>
<div style="position:absolute; top:2%; left:5%; width:91%;">


<?php 
if(!$conf->global->HOSPEDAJE_MYPARAM_HELP){
    print info_admin($langs->trans("avisoguest")); 
};
?>

<center>
<?php

	print '<input disabled type="text" class="takepospay" id="txt_people_total" name="people_total" style="width: 50%;" placeholder="'.$langs->trans('Ctd_guest').'">';
	print '<br />';
	print '<input disabled type="text" class="takepospay" id="txt_people_discount" name="people_discount" style="width: 50%;" placeholder="'.$langs->trans('Dto_guest').'">';

?>
</center>
</div>
<div style="position:absolute; top:33%; left:5%; height:52%; width:92%;">
<br />
<?php
print '<br />';
print '<button type="button" class="calcbutton" onclick="AddReduction(7);">7</button>';
print '<button type="button" class="calcbutton" onclick="AddReduction(8);">8</button>';
print '<button type="button" class="calcbutton" onclick="AddReduction(9);">9</button>';
print '<button type="button" class="calcbutton2" id="btn_people_total" onclick="Ctd(\'e\');">OK</button>';
print '<button type="button" class="calcbutton" onclick="AddReduction(4);">4</button>';
print '<button type="button" class="calcbutton" onclick="AddReduction(5);">5</button>';
print '<button type="button" class="calcbutton" onclick="AddReduction(6);">6</button>';
print '<button type="button" class="calcbutton2"></button>';
print '<button type="button" class="calcbutton" onclick="AddReduction(1);">1</button>';
print '<button type="button" class="calcbutton" onclick="AddReduction(2);">2</button>';
print '<button type="button" class="calcbutton" onclick="AddReduction(3);">3</button>';
print '<button type="button" class="calcbutton3 poscolorblue" onclick="Reset();"><span id="printtext" style="font-weight: bold; font-size: 18pt;">C</span></button>';
print '<button type="button" class="calcbutton" onclick="AddReduction(0);">0</button>';
print '<button type="button" class="calcbutton" id="focusCant"><i class="fa fa-arrow-circle-up" aria-hidden="true"></i></button>';
print '<button type="button" class="calcbutton" id="focusDesc">&nbsp;<i class="fa fa-arrow-circle-down" aria-hidden="true"></i></button>';
print '<button type="button" class="calcbutton3 poscolordelete" onclick="parent.$.colorbox.close();"><span id="printtext" style="font-weight: bold; font-size: 18pt;">X</span></button>';

?>
</div>

<script type="text/javascript" language="javascript">

$( document ).ready(function() {	
	
	$( "#txt_people_total" ).select();
	$('#focusCant').html('<i class="fa fa-i-cursor" aria-hidden="true"></i>');
	
	$("#txt_people_total").focus(function(){
		inFocus = true;
	});

	$("#txt_people_discount").focus(function(){
		inFocus = false;
	});
	
});

var inFocus = true;	
var peopleTotal = '';
var peopleDiscount = '';
var numline = 0;

		function Reset()
    	{
    		peopleTotal = '';
    		jQuery('#txt_people_total').val(peopleTotal);
    	
    		peopleDiscount = '';
    		jQuery('#txt_people_discount').val(peopleDiscount);

    		if(inFocus){
				$('#focusCant').html('<i class="fa fa-i-cursor" aria-hidden="true"></i>');
				$('#focusDesc').html('<i class="fa fa-arrow-circle-down" aria-hidden="true"></i>');
        	}else{
        		$('#focusDesc').html('<i class="fa fa-i-cursor" aria-hidden="true"></i>');
				$('#focusCant').html('<i class="fa fa-arrow-circle-up" aria-hidden="true"></i>');
            }
    	}
		function AddReduction(peopleNumber)
    	{

			if(inFocus){
				peopleTotal += String(peopleNumber);
    			$("#txt_people_total").val(peopleTotal);
			}else{
				peopleDiscount += String(peopleNumber);
    			jQuery("#txt_people_discount").val(peopleDiscount);
			}


    	}
		function Ctd(number)
		{
			if (number === 'e') 
			{
				ValidateCtdPeople();
			}
		}
		function ValidateCtdPeople()
		{
        	var peopleNumber = parseFloat(peopleTotal);
        	var guestNumber = parseFloat(peopleDiscount);
        	numline = parent.$('#tablelines tbody').find('tr.selected').prop("id");
    		if (isNaN(peopleNumber) || isNaN(guestNumber) ) {
    			console.error('Error not a valid number');
    			return;
    		}
    		window.location.href = 'hospedaje_selected_guest.php?idline='+numline+'&persons='+peopleNumber+'&guest='+guestNumber+'&place=<?php echo $place;?>';
    		//parent.$.colorbox.close();
		}

		$('#focusCant').on('click',function(ev){
			inFocus = true;
			$(this).html('<i class="fa fa-i-cursor" aria-hidden="true"></i>');
			$('#focusDesc').html('<i class="fa fa-arrow-circle-down" aria-hidden="true"></i>');
			$("#txt_people_total").focus(function(){
				inFocus = false;
			});
		});
			

		$('#focusDesc').on('click',function(ev){
			inFocus = false;
			$(this).html('<i class="fa fa-i-cursor" aria-hidden="true"></i>');
			$('#focusCant').html('<i class="fa fa-arrow-circle-up" aria-hidden="true"></i>');
			$("#txt_people_discount").focus(function(){
				inFocus = true;
			});
		});
		
</script>
<?php 