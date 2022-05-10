<?php
/**
 *	\file       hospedaje/hospedajeindex.php
 *	\ingroup    hospedaje
 *	\brief      Home page of hospedaje top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT .'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("hospedaje@hospedaje"));

$action = GETPOST('action', 'aZ09');
$place = (GETPOST('place', 'aZ09') ? GETPOST('place', 'aZ09') : 0);
$placeid = 0; //id de la factura
$invoiceid = GETPOST('invoiceid', 'int');
//$idproduct = GETPOST('idproduct', 'int');


// Security check
// if (! $user->rights->hospedaje->myobject->read) {
// 	accessforbidden();
// }
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();

global $mysoc;


/*
 * ACTIONS ADD HOSPEDAJE
 */


$qty = GETPOST('qty', 'int');
$descguest = GETPOST('descguest', 'int');
$desc = GETPOST('desc', 'alpha');
$number = GETPOST('number', 'aZ09');

if ( $action == "dayrow" && $placeid == 0 ){
    
    /*$prod = new Product($db);
    $prod->fetch($idproduct);
    $datapriceofproduct = $prod->getSellPrice($mysoc, $customer, 0);
    $tva_npr = $datapriceofproduct['tva_npr'];*/
    
    $invoice = new Facture($db);
    if ($invoiceid > 0) {
    	$ret = $invoice->fetch($invoiceid);
    } else {
    	$ret = $invoice->fetch('', '(PROV-POS'.$_SESSION["takeposterminal"].'-'.$place.')');
    }
    if ($ret > 0) {
	$placeid = $invoice->id;
    }
    
    $customer = new Societe($db);
    $customer->fetch($invoice->socid);
    
    $tva_tx = GETPOST('tva_tx', 'alpha');
    if ($tva_tx != '') {
        if (!preg_match('/\((.*)\)/', $tva_tx)) {
            $tva_tx = price2num($tva_tx);
        }
    } else {
        $tva_tx = get_default_tva($mysoc, $customer);
    }
    
    // Local Taxes
    /*$localtax1_tx = get_localtax($tva_tx, 1, $customer, $mysoc, $tva_npr);
    $localtax2_tx = get_localtax($tva_tx, 2, $customer, $mysoc, $tva_npr);*/
    
    $retu = $invoice->addline($desc, $number,$qty, $tva_tx, 0, 0, 0, $descguest, '', 
                        0, 0, 0, '', 'TTC', $number, 0, -1, 0, '', 0, 0, null, '', '', 0, 100, '', null, 0);
                        
    if($retu > 0){
        $invoice->fetch($placeid);
    }
    
}








































