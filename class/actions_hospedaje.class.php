<?php
/* Copyright (C) 2021 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

//require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';

/**
 * \file    hospedaje/class/actions_hospedaje.class.php
 * \ingroup hospedaje
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsHOSPEDAJE
 */
class ActionsHOSPEDAJE
{
	/** 
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	
	/**
	 * Facture id
	 * @var int
	 */
	public $id;

	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
		
		$invoiceid = (GETPOST('invoiceid', 'int') ? GETPOST('invoiceid', 'int') : 0);
		$place = (GETPOST('place', 'aZ09') ? GETPOST('place', 'aZ09') : 0);
		$invoice = new Facture($db);
		if ($invoiceid > 0) {
		    $ret = $invoice->fetch($invoiceid);
		} else {
		    $ret = $invoice->fetch('', '(PROV-POS'.$_SESSION["takeposterminal"].'-'.$place.')');
		}
		if ($ret > 0) {
		    $placeid = $invoice->id;
		}
		$this->id = $placeid;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					<0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *                            				>0 if OK and we want to replace standard actions.
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs, $db;

		$error = 0; // Error counter
		//print_r($parameters);
		/*print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1'))) {	    // do something only for the context 'somecontext1' or 'somecontext2'
			// Do what you want here...
			// You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.		       
		}

		if (!$error) {
		    $this->results = array('re' => 999 );
			$this->resprints = 'A text to show';
			return 1; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		
		    // do something only for the context 'somecontext1' or 'somecontext2'
			foreach ($parameters['toselect'] as $objectid) {
				// Do action on each object id
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("HOSPEDAJEMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int 		            <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$langs->load("hospedaje@hospedaje");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'hospedaje') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("HOSPEDAJE");
			$this->results['picto'] = 'hospedaje@hospedaje';
		}

		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		return 1;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int 		      			  	<0 if KO,
	 *                          				=0 if OK but we want to process standard actions too,
	 *  	                            		>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->rights->hospedaje->myobject->read) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Execute action completeTabsHead
	 *
	 * @param   array           $parameters     Array of parameters
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         'add', 'update', 'view'
	 * @param   Hookmanager     $hookmanager    hookmanager
	 * @return  int                             <0 if KO,
	 *                                          =0 if OK but we want to process standard actions too,
	 *                                          >0 if OK and we want to replace standard actions.
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user;

		if (!isset($parameters['object']->element)) {
			return 0;
		}
		if ($parameters['mode'] == 'remove') {
			// utilisé si on veut faire disparaitre des onglets.
			return 0;
		} elseif ($parameters['mode'] == 'add') {
			$langs->load('hospedaje@hospedaje');
			// utilisé si on veut ajouter des onglets.
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['context1', 'context2'])) {
				$datacount = 0;

				$parameters['head'][$counter][0] = dol_buildpath('/hospedaje/hospedaje_tab.php', 1) . '?id=' . $id . '&amp;module='.$element;
				$parameters['head'][$counter][1] = $langs->trans('HOSPEDAJETab');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'hospedajeemails';
				$counter++;
			}
			if ($counter > 0 && (int) DOL_VERSION < 14) {
				$this->results = $parameters['head'];
				// return 1 to replace standard code
				return 1;
			} else {
				// en V14 et + $parameters['head'] est modifiable par référence
				return 0;
			}
		}
	}

	
	/* Add here any other hooked methods... */
	public function ActionButtons($parameters, &$object, &$action, $hookmanager)
	{
	    global $conf, $user, $langs, $db;
	    
	    // Security check
	    if ($user->rights->hospedaje->hospedaje->posbutton) {
	    
	    //print_r($parameters);
	    $error = 0; // Error counter

	    /* print_r($parameters); print_r($object); echo "action: " . $action; */
	    if (in_array($parameters['currentcontext'], array('takeposfrontend'))) {	    // do something only for the context 'somecontext1' or 'somecontext2'
	        // Do what you want here...
	        // You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.
	        $idinvoice = (GETPOST('invoiceid', 'int')? GETPOST('invoiceid', 'int') : 0);
	        $factureid = !empty($this->id)? $this->id : -1;

	        $langs->loadLangs(array("hospedaje@hospedaje"));

	        $htmlcode = '<header class="navbar" style="margin:0 1em 0 1em;">';
	        $htmlcode.= '<section class="navbar-section"></section>';
	        $htmlcode.= '<section class="navbar-center"><i class="fa fa-location-arrow" aria-hidden="true"></i>';
	 		$htmlcode.='<span id="placesel">&nbsp;0</span>&nbsp;|&nbsp;<i class="fa fa-map-marker" aria-hidden="true"></i>';
	 		$htmlcode.='<span id="zonesel">&nbsp;0</span></section><section class="navbar-section">';
	 		if(!$conf->global->HOSPEDAJE_MYPARAM_HELP){
    	 		$htmlcode.='<div class="popover popover-left ">';
    	 		$htmlcode.='<span style="cursor: pointer;">';
    	 		$htmlcode.='<i class="fa fa-info-circle" aria-hidden="true"></i>';
    	 		$htmlcode.='</span>';
    	 		$htmlcode.='<div class="popover-container">';
    	 		$htmlcode.='<div class="card">';
    	 		$htmlcode.='<div class="card-header">';
    	 		$htmlcode.='';
    	 		$htmlcode.='</div>';
    	 		$htmlcode.='<div class="card-body">';
    	 		$htmlcode.=$langs->trans("zonetxtheaderhelp");
    	 		$htmlcode.='</div>';
    	 		$htmlcode.='<div class="card-footer">';
    	 		$htmlcode.='';
    	 		$htmlcode.='</div>';
    	 		$htmlcode.='</div>';
    	 		$htmlcode.='</div>';
    	 		$htmlcode.='</div>';
	 		};
	 		$htmlcode.='</section></header>';
	        
	        ?>
	        <style>
	        .navbar{align-items:stretch;display:-ms-flexbox;display:flex;-ms-flex-align:stretch;-ms-flex-pack:justify;-ms-flex-wrap:wrap; flex-wrap:wrap;justify-content:space-between;}.navbar .navbar-section{align-items:center;display:-ms-flexbox;display:flex;-ms-flex:1 0 0;flex:1 0 0;-ms-flex-align:center;}.navbar .navbar-section:not(:first-child):last-child{-ms-flex-pack:end;justify-content:flex-end;}.navbar .navbar-center{ align-items:center;display:-ms-flexbox; display:flex;-ms-flex:0 0 auto;flex:0 0 auto;ms-flex-align:center;padding:5px;}.navbar .navbar-brand{font-size:.9rem;text-decoration:none;}
	        .popover{display:inline-block;position:relative}.popover .popover-container{left:50%;opacity:0;padding:.4rem;position:absolute;top:0;transform:translate(-50%,-50%) scale(0);transition:transform .2s;width:320px;z-index:300}.popover :focus+.popover-container,.popover:hover .popover-container{display:block;opacity:1;transform:translate(-50%,-100%) scale(1)}.popover.popover-right .popover-container{left:100%;top:50%}.popover.popover-right :focus+.popover-container,.popover.popover-right:hover .popover-container{transform:translate(0,-50%) scale(1)}.popover.popover-bottom .popover-container{left:50%;top:100%}.popover.popover-bottom :focus+.popover-container,.popover.popover-bottom:hover .popover-container{transform:translate(-50%,0) scale(1)}.popover.popover-left .popover-container{left:0;top:50%}.popover.popover-left :focus+.popover-container,.popover.popover-left:hover .popover-container{transform:translate(-100%,-50%) scale(1)}.popover .card{border:0;box-shadow:0 .2rem .5rem rgba(48,55,66,.3)}
	        .card{background:#fff;border:.05rem solid #dadee4;border-radius:.1rem;display:-ms-flexbox;display:flex;-ms-flex-direction:column;flex-direction:column}.card .card-body,.card .card-footer,.card .card-header{padding:.8rem;padding-bottom:0}.card .card-body:last-child,.card .card-footer:last-child,.card .card-header:last-child{padding-bottom:.8rem}.card .card-body{-ms-flex:1 1 auto;flex:1 1 auto}.card .card-image{padding-top:.8rem}.card .card-image:first-child{padding-top:0}.card .card-image:first-child img{border-top-left-radius:.1rem;border-top-right-radius:.1rem}.card .card-image:last-child img{border-bottom-left-radius:.1rem;border-bottom-right-radius:.1rem}
	        </style>
	        
			<script type="text/javascript">

            jQuery(document).ready(function() {
            	$.getJSON("<?php echo DOL_URL_ROOT.'/custom/hospedaje/hospedaje_selected_site.php'; ?>",
	 		 			{action: "getzone", invoideid: <?php echo $factureid; ?> , token:'<?php echo currentToken(); ?>'}, function( data ) {
		 		 			console.log(data);
            				$( "#placesel" ).html("<b>&nbsp;"+data.label+"</b>");
            				$( "#zonesel" ).html("<b>&nbsp;"+data.zone+"</b>");
		 			});

            	
	 				$('<?php echo $htmlcode;?>').insertBefore(parent.$("#poslines")).find(parent.$("#tablelines"));
				
	 			parent.$("#poslines").on("click",function(event){
	 				event.preventDefault();
	 				id = document.getElementById('invoiceid');
	 				if(id !== null){
	 				$.getJSON("<?php echo DOL_URL_ROOT.'/custom/hospedaje/hospedaje_selected_site.php'; ?>",
	 		 			{action: "getzone", invoideid: id.value , token:'<?php echo currentToken(); ?>'}, function( data ) {
		 		 			console.log(data);
            				$( "#placesel" ).html("<b>&nbsp;"+data.label+"</b>");
            				$( "#zonesel" ).html("<b>&nbsp;"+data.zone+"</b>");
		 			});
	 				}
	 			});
			});
			/**/
            jQuery(document).ready(function() {
            	parent.$("#delete").on("click",function(event){
            		id = document.getElementById('invoiceid');
	 				event.preventDefault();
	 				$.getJSON("<?php echo DOL_URL_ROOT.'/custom/hospedaje/hospedaje_selected_site.php'; ?>",
		 		 			{action: "getzone", invoideid: id.value , token:'<?php echo currentToken(); ?>'}, function( data ) {
			 		 			console.log(data);
			 		 			parent.$("#poslines").load("<?php echo DOL_URL_ROOT; ?>/takepos/invoice.php", function() {
			 		 				parent.$(this).find("tr:eq(1)").click().addClass("selected");
			 		 			});
	            				$( "#placesel" ).html("<b>&nbsp;"+data.label+"</b>");
	            				$( "#zonesel" ).html("<b>&nbsp;"+data.zone+"</b>");
			 			});
	 			});
            });   
		    
    		</script>
    		<?php
            
            $menu = [
                ['title'=>'<span class="fa fa-map-marker paddingrightonly"></span><div class="trunc">'.$langs->trans("Site").'</div>', 'action'=>'$.colorbox({href:\'../custom/hospedaje/hospedaje_selected_site.php?place=\'+place, width:\'80%\', height:\'80%\', transition:\'elastic\', iframe:\'false\', title:\''.$langs->trans("Sites").'\'});']
                ,
                ['title'=>'<span class="fa fa-user paddingrightonly"></span><div class="trunc">'.$langs->trans("Persons").'</div>', 'action'=>'$.colorbox({href:\'../custom/hospedaje/hospedaje_selected_people.php?place=\'+place, width:\'80%\', height:\'80%\', transition:\'elastic\', iframe:\'false\', title:\''.$langs->trans("Persons").'\'});']
	            ,
                ['title'=>'<span class="fa fa-users paddingrightonly"></span><div class="trunc">'.$langs->trans("Guest").'</div>', 'action'=>'$.colorbox({href:\'../custom/hospedaje/hospedaje_selected_guest.php?place=\'+place, width:\'80%\', height:\'80%\', transition:\'elastic\', iframe:\'false\', title:\''.$langs->trans("Guest").'\'});']
	            ,
                ['title'=>'<span class="fa fa-clock paddingrightonly"></span><div class="trunc">'.$langs->trans("Days").'</div>', 'action'=>'$.colorbox({href:\'../custom/hospedaje/hospedaje_selected_date.php?place=\'+place, width:\'80%\', height:\'80%\', transition:\'elastic\', iframe:\'true\', title:\''.$langs->trans("Days").'\'});']
            ]; 
            //return $menu; //version 14 descomentar esta linea

	    }
	   
	    if (!$error) {
	       
	        $btn_place = $menu[0];
	        $btn_guest = $menu[1];
	        $btn_persons = $menu[2];
	        $btn_days = $menu[3];
	        
	        $this->results = array([$btn_place,$btn_days,$btn_persons,$btn_guest]);
            
	        //$this->resprints = 'A text to show';
	        return 0; // or return 1 to replace standard code
	    } else {
	        $this->errors[] = 'Error message';
	        return -1;
	    }
	    }
	}
	

}