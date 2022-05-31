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
$date_start = GETPOST('date_start', 'int');
$date_end = GETPOST('date_end', 'int');
$days = GETPOST('days', 'ing');

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


if ( !empty($days) and $days > 0 ) {

    $head='<meta name="apple-mobile-web-app-title" content="TakePOS"/>
    <meta charset=UTF-8>
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <link rel="stylesheet" href="../../takepos/css/pos.css.php">';
    top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss);
    
    /*
     * Actions
     */
 
    console_log($idline);
    
    function function_($milliseconds) {
        $seconds = $milliseconds / 1000;
        return date("d-m-Y", $seconds);
    }
    
    ?>
	<script type="text/javascript" src="../../takepos/js/jquery.colorbox-min.js"></script>
	<script>
	$( document ).ready(function() {	
		$('#loader').hide();
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
                	foreach($invoice->lines as $row){
                       if ($idline == $row->id) {
                	?>
    					var descp = "<?php echo dol_escape_js($days.'%20'.$langs->trans('xtt0').','.'%20'.$langs->trans('from').'%20'.function_($date_start).'%20'.$langs->trans('to').'%20'.function_($date_end)); ?>";
    					var descr = "<?php echo dol_escape_js($langs->trans('Reduction')); ?>";
    					parent.$("#poslines").load("invoice.php?action=freezone&place=<?php echo $place; ?>&number=-"+<?php echo $row->total_ttc ;?>+"&desc="+descr, function() {
    					parent.$("#poslines").load("<?php echo DOL_URL_ROOT; ?>/custom/hospedaje/hospedaje_actions.php?place=<?php echo $place; ?>&invoiceid=<?php echo $placeid; ?>&action=dayrow&desc="+descp+"&number=<?php echo $row->total_ttc ;?>&qty=<?php echo $days; ?>", function() {
    	                	//parent.$("#poslines").load("invoice.php?action=addnote&place=<?php echo $place; ?>&invoiceid=<?php echo $placeid ;?>&idline=<?php echo $idline ;?>&addnote=<?php echo $days.'%20'.$langs->trans('xtt0').','.'%20'.$langs->trans('from').'%20'.function_($date_start).'%20'.$langs->trans('to').'%20'.function_($date_end) ;?> ", function() {
        						parent.$("#poslines").load("invoice.php?place=<?php echo $place; ?>&invoiceid=<?php echo $placeid ;?> ", function() {
    			 		 			parent.$(this).find("tr:eq(1)").click().addClass("selected");
                                    parent.$.colorbox.close();
       	                        });
                            //});
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
                 		alert("<?php echo $langs->trans('alerta1')?>");
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
<link rel="stylesheet" href="../../takepos/css/pos.css.php">
<script src="js/moment.js"></script>
<script src="js/locale/es.js"></script>
<link rel="stylesheet" href="css/spectrecss/spectre.min.css">
<link rel="stylesheet" href="css/spectrecss/spectre-exp.min.css">
<link rel="stylesheet" href="css/spectrecss/spectre-icons.min.css">


';
top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss);

print '<style type="text/css">

.error {
	text-align: center;
	background: Silver;
	color: blue;
}
.highlight {
	text-align: center;
	background: Silver;
	color:Green;
}

.centrediv {
  display: flex;
  justify-content: center;
  align-items: center;   
}
html {
    height: 100%;
}
body {
    min-height: 100%;
}


.datebox {
    margin: 5px auto 0;
    width:120px;
    font-size: 17px;
    cursor: pointer;
}

.avizone{
    display: inline-block;
    height:30px;
    vertical-align: middle;
    border-top : solid 3px transparent;
    border-bottom : solid 3px transparent;
}


</style>';


$form = new Form($db);
print '<div class="centrediv">';


print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST" id="target" >';
print '<input type="hidden" name="token" value="'.newToken().'">';

print '<div class="container">';

//Texto Aviso
print '<div class="columns">';
print '<div class="column col-12 col-xs-12">';
if(!$conf->global->HOSPEDAJE_MYPARAM_HELP){
    print info_admin($langs->trans("aviso1"));
};
print '</div>';
print '</div>';

//Texto Cabecera 2
print '<div class="columns">';
print '<div class="column col-12 col-xs-12">';
print '<span class="column col-12 col-xs-12">';
print $langs->trans('titletb');
print '</span>';
print '</div>';
print '</div>';

print '<hr />';

//DATEPICKERS 1
print '<div class="columns">';
print '<div class="column col-12 col-xs-12">';
//print "<div id='date_start'></div>";
print $form->selectDate(dol_now('auto'),'date_start',1,1,0,'',1,1,0,0,'','','',0,'','','auto');
print '</div>';
print '</div>';

print '<hr />';

//DATEPICKERS 2
print '<div class="columns">';
print '<div class="column col-12 col-xs-12">';
//print "<div id='date_end'></div>";
print $form->selectDate(dol_now('auto'),'date_end',1,1,0,'',1,1,0,0,'','','',0,'','','auto');
print '</div>';
print '</div>';


print '<hr />';


//Leyenda de dias
print '<div class="columns">';
print '<div class="column col-12 col-xs-12">';
print '<span class="column col-12 col-xs-12" id="textinput"></span><hr/>';
//Texto de dias
print '<div class="highlight">';
print'<h1 id="p_dias">';
echo "0 ".$langs->trans("xtt0");
print '</h1>';
print '</div>';
print '</div>';
print '</div>';

    
//Boton
print '<div class="columns">';
print '<div class="column col-12 col-xs-12">';
//print '<div style="display: flex;align-items: center; ">';
print '<button id="sendbtn" type="submit" class="btn btn-primary btn-lg btn-block" name="save"><i class="icon icon-time"></i> ';
print $langs->trans("btnSelect");
print '</button>';
print '<button style="display: none;" id="loader" class="btn btn-primary loading btn-lg btn-block"></button>';
//print '<img style="display: none;" id="loader" src="img/ajax-loader.gif" height="20px">';
//print '</div>';
print '</div>';
print '</div>';

//Texto de zona
print '<br />';
print '<span style="display: none;" id="zonetext" class="avizone">';
print '</span>';
print '<div style="display: none;" class="carga loading"></div>';
print '<br />';

print '</div>';
print '<hr />';

print '</form>';
print '</div>';
?>

<script type="text/javascript" language="javascript">

var date_entrada = "";
var date_salida = "";
var dias = 0;

function diasHospedaje(){
	date_entrada = moment(moment($("#date_start").datepicker("getDate")).format("YYYY-MM-DD")+'T'+$("#date_starthour").val()+':'+$("#date_startmin").val()).valueOf();//$( "#date_start" ).datepicker( "getDate" );
	date_salida  = moment(moment($("#date_end").datepicker("getDate")).format("YYYY-MM-DD")+'T'+$("#date_endhour").val()+':'+$("#date_endmin").val()).valueOf();//$( "#date_end" ).datepicker( "getDate" );
    diference = parseInt((date_salida - date_entrada) /(1000*60*60*24));
    validate(diference);
  	return diference;
}

function validate(arg){
 	if(arg < 0){
  		$( "#p_dias" ).addClass("error");
    }else if(arg >= 0){
    	$( "#p_dias" ).removeClass("error");
      	$( "#p_dias" ).addClass("highlight");
    }
}



 jQuery(document).ready(function() {

	 $( "#txtaviso" ).show();
	 $( '#zonetext' ).hide();
	 
	 //Numero de factura
	 var invoiceid = parent.$("#invoiceid").val();
	 //console.log("INVOICE ID: "+invoiceid);
	 
	 /*Establecemos la fecha actual no menor al dia del sistema*/
	 $( "#date_start" ).datepicker( { minDate: "+d" } ).removeClass("maxwidthdate").addClass("datebox");
     $( "#date_start" ).datepicker( "option", "minDate", "+d" );
     $( "#date_end" ).datepicker( { minDate: "+d" } ).removeClass("maxwidthdate").addClass("datebox");
     $( "#date_end" ).datepicker( "option", "minDate", "+d" );

     /*Actualizamos hipertex*/
	 function reloaded(){
		 $('.carga').show();
		 $('#zonetext').hide();
		dias = diasHospedaje();
    	ENTRADA = moment($("#date_start").datepicker("getDate")).format("YYYY-MM-DD")+'T'+$("#date_starthour").val()+':'+$("#date_startmin").val();
    	SALIDA = moment($("#date_end").datepicker("getDate")).format("YYYY-MM-DD")+'T'+$("#date_endhour").val()+':'+$("#date_endmin").val();
		$( "#p_dias" ).html(dias +" <?php echo $langs->trans('xtt0')?> ");
		$('#textinput').html('<?php echo $langs->trans('xtt1')?> '+ moment(ENTRADA).format("LLLL") +' <?php echo $langs->trans('xtt2')?> '+ moment(SALIDA).format("LLLL"));
		/*Actualizamos la fecha de la zona si es elegida si no null*/
		if(invoiceid>0){
        	$.ajax({
        		type: "POST",
        		url: "<?php echo DOL_URL_ROOT.'/custom/hospedaje/hospedaje_selected_site.php'; ?>",
        		data: { action: "updateDates", invoiceid: invoiceid , startdate:date_entrada , enddate:date_salida , token: '<?php echo newToken(); ?>' }
        	}).done(function( response ) {
        		console.log(response);
        		if(response==0){
            		$('#zonetext').show();
            		$('.carga').hide();
            		$('#zonetext').html('<h6><?php echo $langs->trans('zonextt1')?></h6>');
            		}else{
            			$('#zonetext').show();
            			$('.carga').hide();
            			$('#zonetext').html('<h6><?php echo $langs->trans('zonextt2')?> '+JSON.parse(response)+'</h6>');
                		}
        	}).fail(function (jqXHR, textStatus) {
        		$('.carga').hide();
        		console.log(textStatus);
        	});
    	}else{$('.carga').hide();};
	};

	 $('#textinput').html('<?php echo $langs->trans('xtt1')?> '+  moment($("#date_start").datepicker( "getDate" )).format("LLLL")  +' <?php echo $langs->trans('xtt2')?> '+ moment($( "#date_end" ).datepicker( "getDate" )).format("LLLL"));
			var nota = '';
			var numline = 0;
    		$( "#date_start" ).change(function() {
    			reloaded()
    			});
            $( "#date_end" ).change(function() {
            	reloaded()
              });
            $( "#date_starthour" ).change(function() {
    			reloaded()
    			});
            $( "#date_endhour" ).change(function() {
            	reloaded()
              });
            $( "#date_startmin" ).change(function() {
    			reloaded()
    			});
            $( "#date_endmin" ).change(function() {
            	reloaded()
              });
    		
            $( "#target" ).submit(function( event ) {
                event.preventDefault();
                $('#loader').show();
                $('#sendbtn').hide();
                dias = diasHospedaje();
                numline = parent.$('#tablelines tbody').find('tr.selected').prop("id");
        		//console.log(numline);
                if(dias===0){
                	parent.$.colorbox.close();
                }
				window.location.href = 'hospedaje_selected_date.php?idline='+numline+'&days='+dias+'&dates='+dias+'&date_start='+new moment(date_entrada).valueOf()+'&date_end='+new moment(date_salida).valueOf()+'&place=<?php echo $place;?>';
            });
    });
</script>
<?php 