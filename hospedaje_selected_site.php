<?php

date_default_timezone_set('America/Argentina/Buenos_Aires');

/**
 * Acampe
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
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

$action = GETPOST('action', 'aZ09');
$place = (GETPOST('place', 'aZ09') ? GETPOST('place', 'aZ09') : 0);
$zone = GETPOST('zone', 'int');
if ($zone == "") {
    $zone = 1;
}

$invoiceid = (GETPOST('invoiceid', 'int') ? GETPOST('invoiceid', 'int') : 0);
$invoideid = (GETPOST('invoideid', 'int') ? GETPOST('invoideid', 'int') : 0);

$startdate = GETPOST('startdate', 'alpha');
$enddate = GETPOST('enddate', 'alpha');

$left = GETPOST('left', 'alpha');
$top = GETPOST('top', 'alpha');
$newname = GETPOST('newname', 'alpha');
$mode = GETPOST('mode', 'alpha');

$now = dol_now('tzserver')*1000;


// Security check
if (! $user->rights->hospedaje->hospedaje->posbutton) {
    accessforbidden();
}

/*
 * Actions
 */

if ($action == "getTables") {
    //require_once DOL_DOCUMENT_ROOT.'/custom/hospedaje/class/zonas.class.php';
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."hospedaje_zonas where zone = ".((int) $zone)." ";
    $resql = $db->query($sql);
    $rows = array();
    while ($row = $db->fetch_array($resql)) {
        $invoice = new Facture($db);
        $result = $invoice->fetch($row['invoideid']);
        if ($result > 0 && ($row['enddate'] >= $now && (int)$row['active'] == 1 )) {
            $row['occupied'] = "red";
        }
        $rows[] = $row;
    }
    echo json_encode($rows);
    exit;
}

if ($action == "update") {
    if ($left > 95) {
        $left = 95;
    }
    if ($top > 95) {
        $top = 95;
    }
    if ($left > 3 or $top > 4) {
        $db->query("UPDATE ".MAIN_DB_PREFIX."hospedaje_zonas set leftpos = ".((int) $left).", toppos = ".((int) $top)." WHERE rowid = ".((int) $place));
    } else {
        $db->query("DELETE from ".MAIN_DB_PREFIX."hospedaje_zonas where rowid = ".((int) $place));
    }
}

if ($action == "updatename") {
    $newname = preg_replace("/[^a-zA-Z0-9\s]/", "", $newname); // Only English chars
    if (strlen($newname) > 3) {
        $newname = substr($newname, 0, 3); // Only 3 chars
    }
    $db->query("UPDATE ".MAIN_DB_PREFIX."hospedaje_zonas SET label='".$db->escape($newname)."' WHERE rowid = ".((int) $place));
}

if ($action == "add") {    
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."hospedaje_zonas(label, leftpos, toppos, zone, active, startdate, enddate, invoideid, fk_user_creat ) VALUES ('', '45', '45', ".((int)$zone).", 0,".$now.",".$now.", 0, $user->id)";
    $asdf = $db->query($sql);
    $db->query("UPDATE ".MAIN_DB_PREFIX."hospedaje_zonas SET label=rowid where label=''");
}


/**/
if ($action == "updateplace") {
    $sql = 'SELECT rowid, active, invoideid, startdate, enddate';
    $sql .= ' FROM '.MAIN_DB_PREFIX.'hospedaje_zonas as t';
    $sql .= ' WHERE t.invoideid = '.((int)$invoiceid);
    $result = $db->query($sql);
    while($row = $db->fetch_array($result)) {
        if ($result > 0){
            $db->query("UPDATE ".MAIN_DB_PREFIX."hospedaje_zonas SET startdate=".$now.",enddate=".$now.", active='0', invoideid='0' WHERE rowid = ".(int)$row['rowid']." AND active='1' AND invoideid= ".(int)$row['invoideid']." ") ;
            $asdf = $db->query($sql);
            $db->query("UPDATE ".MAIN_DB_PREFIX."hospedaje_zonas SET startdate=".$row['startdate'].", enddate=".$row['enddate'].", invoideid=".((int)$invoiceid)."  WHERE rowid = ".((int)$place) );
            continue;
        }
    }
    $res = $db->query("UPDATE ".MAIN_DB_PREFIX."hospedaje_zonas SET active='1', invoideid=".((int)$invoiceid)."  WHERE rowid = ".((int)$place));
    echo json_encode($res);
    exit;  
}

//UPDATE DATE IN TABLE HOSPEDAJE ZONAS
if($action == 'updateDates' && !empty($invoiceid)){
    $num = 0;
    $sql = "SELECT rowid,label FROM ".MAIN_DB_PREFIX."hospedaje_zonas WHERE invoideid = ".(int)$invoiceid." AND active = '1' ";
    $resql = $db->query($sql);
    $num = $db->num_rows($resql);
    $object = $db->fetch_object($resql);
    if ($resql && $num > 0) {
            $db->query("UPDATE ".MAIN_DB_PREFIX."hospedaje_zonas SET startdate=".trim($startdate).",enddate=".trim($enddate)."  WHERE rowid = ".(int)$object->rowid."  ");
            echo json_encode($object->label);
            exit;
        }else{
            echo json_encode(0);
            exit;
            //dol_print_error($db);
        }
}

if($action == 'getzone'){
    
    if (!empty($invoideid)) {
        $sql = "SELECT zone, label FROM ".MAIN_DB_PREFIX."hospedaje_zonas WHERE invoideid=".((int)$invoideid);
        $resql = $db->query($sql);
        $obj = $db->fetch_object($resql);
        if ($obj && $obj !== null) {
            echo json_encode(array('label'=>$obj->label,'zone'=>$obj->zone));
            exit;
        }
        echo json_encode(array('label'=>0,'zone'=>0));
        exit;
    }
    echo json_encode(array('label'=>0,'zone'=>0));
    exit;
    //the end
}

/*revisar #310520221133
if($action == 'consulta'){
    $query = "SELECT enddate FROM ".MAIN_DB_PREFIX."hospedaje_zonas WHERE invoideid=".((int)$invoiceid);
    $elements = $db->fetch_row($db->query($query));
    if(is_array($elements)){
        echo json_encode((int)implode($elements));
       exit; 
    }else{
        echo json_encode($now);
        exit;
    }
    
}*/


/*
 * View
 */

// Title
$title = 'Hospedaje - Dolibarr '.DOL_VERSION;
if (!empty($conf->global->MAIN_APPLICATION_TITLE)) {
    $title = 'Hospedaje - '.$conf->global->MAIN_APPLICATION_TITLE;
}
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
?>

<style type="text/css">
div.tablediv{
background-image:url(img/table.svg);
-moz-background-size:100% 100%;
-webkit-background-size:100% 100%;
background-size:100% 100%;
height:10%;
width:10%;
text-align: center;
font-size:250%;
color:#531414;
}
div.tablediv:hover{
cursor: -webkit-grab; 
cursor: grab;
}
div.tablediv:active{
cursor: -webkit-grabbing; 
cursor: grabbing;
}

div.red{
color:red;
}
html {
    height: 100%;
}
body {
    min-height: 100%;
}

.pointer{cursor: pointer;}


</style>
<script>
var DragDrop='<?php echo $langs->trans("DragDropZone"); ?>';

function updateplace(idplace, left, top) {
	console.log("updateplace idplace="+idplace+" left="+left+" top="+top);
	$.ajax({
		type: "POST",
		url: "<?php echo DOL_URL_ROOT.'/custom/hospedaje/hospedaje_selected_site.php'; ?>",
		data: { action: "update", left: left, top: top, place: idplace, token: '<?php echo currentToken(); ?>' }
	}).done(function( msg ) {
		window.location.href='hospedaje_selected_site.php?mode=edit&zone=<?php echo urlencode($zone); ?>';
	});
}

function updatename(rowid) {
	var after=$("#tablename"+rowid).text();
	console.log("updatename rowid="+rowid+" after="+after);
	$.ajax({
		type: "POST",
		url: "<?php echo DOL_URL_ROOT.'/custom/hospedaje/hospedaje_selected_site.php'; ?>",
		data: { action: "updatename", place: rowid, newname: after, token: '<?php echo currentToken(); ?>' }
	}).done(function( msg ) {
		window.location.href='hospedaje_selected_site.php?mode=edit&zone=<?php echo urlencode($zone); ?>';
	});
}

function LoadPlace(zoneid, invoiceid){
	
	//parent.location.href= "<?php echo DOL_URL_ROOT; ?>/takepos/index.php?place="+zoneid;
	/*$.ajax({
        url : '<?php echo DOL_URL_ROOT; ?>/takepos/index.php',
        data : {place:zoneid, token:'<?php echo currentToken(); ?>'},
        method : 'GET',
        success : function(response){
        	parent.$('body').load(this.url, function() {
				parent.$.colorbox.close();
            });
        },
        error: function(error){
               console.log(error);
        }
	});*/
/**/
	/*$.getJSON( "<?php echo DOL_URL_ROOT.'/custom/hospedaje/hospedaje_selected_site.php'; ?>", 
		{ action: "consulta" , invoiceid: invoiceid, token: '<?php echo currentToken(); ?>' } ,function( respuesta ) {
		 	console.log( respuesta >= moment().valueOf() );
		 	if(respuesta <= moment().valueOf()){
		 		parent.$("#poslines").load("<?php echo DOL_URL_ROOT; ?>/takepos/invoice.php", function() {
		 			$(this).find("tr:gt(0)").click().addClass("selected", function(){
		 				$(this).find("tr:eq(0)").click().addClass("selected");
		 				parent.$("#delete").click();
		 				parent.$.colorbox.close();
			 		});
		 			
	            });
			}else{}});*/
					
		$.ajax({
			type: "POST",
			url: "<?php echo DOL_URL_ROOT.'/custom/hospedaje/hospedaje_selected_site.php'; ?>",
			data: { action: "updateplace", place: zoneid, invoiceid: invoiceid , token: '<?php echo currentToken(); ?>' }
		}).done(function( response ) {
			//console.log(response);
			//window.location.href='hospedaje_selected_site.php?action=getzone&invoideid='+invoiceid+'&token=<?php echo currentToken(); ?>';
			$.ajax({
				type: "GET",
				url: "<?php echo DOL_URL_ROOT; ?>/custom/hospedaje/hospedaje_selected_site.php",
				data: { action: "getzone", invoideid: invoiceid, token:'<?php echo currentToken(); ?>' }
			}).done(function( data ) {
				console.log(data);
				var jsonresponse = JSON.parse(data);
				parent.$("#poslines").load("<?php echo DOL_URL_ROOT; ?>/takepos/invoice.php", function() {
					$(this).find("tr:eq(1)").click().addClass("selected");
					parent.$( "#placesel" ).html("<b>&nbsp;"+jsonresponse.label+"</b>");
					parent.$( "#zonesel" ).html("<b>&nbsp;"+jsonresponse.zone+"</b>");
					parent.$.colorbox.close();
	            });
			});
		});

	

}

$( document ).ready(function() {
	var invoiceid = parent.$("#invoiceid").val();
	//Verificador
	if(invoiceid > 0 || <?php echo $user->admin; ?>){
	
	$.getJSON('./hospedaje_selected_site.php?action=getTables&zone=<?php echo $zone; ?>', function(data) {
		$.each(data, function(key, val) {
			<?php if ($mode == "edit") {?>
			$('body').append('<div class="tablediv" contenteditable onblur="updatename('+val.rowid+');" style="position: absolute; left: '+val.leftpos+'%; top: '+val.toppos+'%;" id="tablename'+val.rowid+'">'+val.label+'</div>');
			$( "#tablename"+val.rowid ).draggable(
				{
					start: function() {
						$("#add").html("<i class='icon icon-delete'></i>&nbsp;<?php echo $langs->trans("Deletezone"); ?>").addClass("btn btn-error");
					},
					stop: function() {
    					var left=$(this).offset().left*100/$(window).width();
    					var top=$(this).offset().top*100/$(window).height();
    					updateplace($(this).attr('id').substr(9), left, top);
					}
				}
			);
			//simultaneous draggable and contenteditable .addClass("icon icon-delete")
			$('#'+val.label).draggable().bind('click', function(){
				$(this).focus();
			})
			<?php } else {?>
				$('body').append('<div class="tablediv '+val.occupied+'" onclick="LoadPlace('+val.rowid+','+invoiceid+');" style="position: absolute; left: '+val.leftpos+'%; top: '+val.toppos+'%;" id="tablename'+val.rowid+'">'+val.label+'</div>');
			<?php } ?>
		});
	});

	}else{
		alert('<?php echo $langs->trans("avisoDropZone"); ?>');	
		parent.$.colorbox.close();
	}
});


</script>
</head>
<body style="overflow: hidden">

<div class="container">
<div class="columns">
<div class="column col-xs-12">

<!--  <div class="clearfix">
  <div class="float-left"></div>
  <div class="float-right">
  	<p><?php echo info_admin($langs->trans("avisowindowszone")); ?><p>
  </div>
</div>-->

<?php if ($user->admin) {?>
<div style="position: absolute; left: 0.1%; top: 0.8%; width:8%; height:11%;">
	<?php if ($mode == "edit") {?>
<a class="btn btn-lg" id="add" onclick="window.location.href='hospedaje_selected_site.php?mode=edit&action=add&token=<?php echo newToken() ?>&zone=<?php echo $zone; ?>';"> <i class="icon icon-copy"></i> <?php echo $langs->trans("Addzone"); ?></a>
	<?php } else { ?>
<a class="btn btn-lg" onclick="window.location.href='hospedaje_selected_site.php?mode=edit&token=<?php echo newToken() ?>&zone=<?php echo $zone; ?>';"> <i class="icon icon-edit"></i> <?php echo $langs->trans("Editzone"); ?></a>
	<?php } ?>
</div>
<?php }
?>
<div style="position: absolute; left: 25%; bottom: 8%; width:50%; height:3%;">
	<center>
	<h1>
	<?php if ($zone > 1) { ?>
	<img class="valignmiddle pointer" src="./img/arrow-prev.png" width="5%" onclick="location.href='hospedaje_selected_site.php?zone=<?php if ($zone > 1) {
	    $zone--; echo $zone; $zone++;
	 } else {
		 echo "1";
	 } ?>';">
	<?php } ?>
	<span class="valignmiddle hide-xs"><?php echo $langs->trans("zone")." ".$zone; ?></span>
	<img src="./img/arrow-next.png" class="valignmiddle pointer" width="5%" onclick="location.href='hospedaje_selected_site.php?zone=<?php $zone++; echo $zone; ?>';">
	</h1>
	</center>
</div>


</div>
</div>
</div>

</body>
</html>


















































