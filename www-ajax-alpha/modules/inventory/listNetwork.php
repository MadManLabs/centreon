<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called  Oreon Inventory  is developped by Merethis company for Lafarge Group,
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset ($oreon))
		exit ();

	# set limit
	$res =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
	$gopt = array_map("myDecode", $res->fetchRow());		
	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewConfiguration"] : $limit = $_GET["limit"];
	isset ($_GET["num"]) ? $num = $_GET["num"] : $num = 0;
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;

	if ($search)
		$rq = "SELECT COUNT(*) FROM host h, inventory_index ii, inventory_manufacturer im WHERE h.host_id = ii.host_id AND " .
			  " h.host_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' AND h.host_id IN (".$oreon->user->lcaHStr.") " .
			  " AND ii.type_ressources";
	else
		$rq = "SELECT COUNT(*) FROM host h, inventory_index ii, inventory_manufacturer im WHERE h.host_id = ii.host_id AND " .
				" h.host_id IN (".$oreon->user->lcaHStr.") AND im.id = ii.type_ressources";
	$res =& $pearDB->query($rq);
	$tmp = & $res->fetchRow();
	$rows = $tmp["COUNT(*)"];
	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", $lang['name']);
	$tpl->assign("headerMenu_desc", $lang['description']);
	$tpl->assign("headerMenu_address", $lang['h_address']);
	$tpl->assign("headerMenu_status", $lang['status']);
	$tpl->assign("headerMenu_manu", $lang['s_manufacturer']);
	# end header menu

	#Host list
	if ($search)
		$rq = "SELECT h.host_id, h.host_name, h.host_alias, h.host_address, h.host_activate, im.alias AS manu_alias FROM host h, inventory_index ii, inventory_manufacturer im WHERE h.host_id = ii.host_id AND " .
			  " h.host_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' AND h.host_id IN (".$oreon->user->lcaHStr.") " .
			  " AND ii.type_ressources ORDER BY h.host_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT h.host_id, h.host_name, h.host_alias, h.host_address, h.host_activate, im.alias AS manu_alias FROM host h, inventory_index ii, inventory_manufacturer im WHERE h.host_id = ii.host_id AND " .
				" h.host_id IN (".$oreon->user->lcaHStr.") AND im.id = ii.type_ressources  " .
				" ORDER BY h.host_name LIMIT ".$num * $limit.", ".$limit;
	$res = & $pearDB->query($rq);
	
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl

	$elemArr = array();
	for ($i = 0; $res->fetchInto($host); $i++) {		
		$selectedElements =& $form->addElement('checkbox', "select[".$host['host_id']."]");	
		if (!$host["host_name"])
			$host["host_name"] = getMyHostName($host["host_template_model_htm_id"]);
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$host["host_name"],
						"RowMenu_link"=>"?p=".$p."&o=o&host_id=".$host['host_id']."&search=".$search,
						"RowMenu_desc"=>$host["host_alias"],
						"RowMenu_address"=>$host["host_address"],
						"RowMenu_status"=>$host["host_activate"] ? $lang["enable"] : $lang["disable"],
						"RowMenu_manu"=>$host["manu_alias"]);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);

	
	#Fill a tab with different page numbers and link
	$pageArr = array();
	for ($i = 0; $i < ($rows / $limit); $i++)
		$pageArr[$i] = array("url_page"=>"./oreon.php?p=".$p."&num=$i&limit=".$limit."&search=".$search,
							"label_page"=>"<b>".($i +1)."</b>",
"num"=> $i);
							
	if($i > 1)							
	$tpl->assign("pageArr", $pageArr);

	$tpl->assign("num", $num);
	$tpl->assign("previous", $lang["previous"]);
	$tpl->assign("next", $lang["next"]);

	if(($prev = $num - 1) >= 0)
	$tpl->assign('pagePrev', ("./oreon.php?p=".$p."&num=$prev&limit=".$limit."&search=".$search));
	if(($next = $num + 1) < ($rows/$limit))
	$tpl->assign('pageNext', ("./oreon.php?p=".$p."&num=$next&limit=".$limit."&search=".$search));
	$tpl->assign('pageNumber', ($num +1)."/".ceil($rows / $limit));
	
	#form select host
	$req = "SELECT id, alias FROM inventory_manufacturer ";
	$res = & $pearDB->query($req);

	$option = array(NULL=>$lang['s_none']);
	for ($i=0;$res->fetchInto($host);$i++) 
    	$option[$host['id']] = $host['alias'];

	$form->addElement('select', 'select_manufacturer', $lang['s_manufacturer'], $option);
		
	#Select field to change the number of row on the page
	for ($i = 10; $i <= 100; $i = $i +10)
		$select[$i]=$i;
	$select[$gopt["maxViewConfiguration"]]=$gopt["maxViewConfiguration"];
	ksort($select);
	$selLim =& $form->addElement('select', 'limit', $lang['nbr_per_page'], $select, array("onChange" => "this.form.submit('')"));
	$selLim->setSelected($limit);
	
	#Element we need when we reload the page
	$form->addElement('hidden', 'p');
	$form->addElement('hidden', 'search');
	$form->addElement('hidden', 'num');
	$tab = array ("p" => $p, "search" => $search, "num"=>$num);
	$form->setDefaults($tab);	

	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listNetwork.ihtml");
	
	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);
	$tpl->display("include/common/legend.ihtml");	
?>