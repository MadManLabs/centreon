<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	if (!isset($oreon))
		exit();

	function getTopologyParent($p)	{
		global $pearDB;
		$rqPath = "SELECT topology_url, topology_url_opt, topology_parent, topology_id, topology_name, topology_page FROM topology WHERE topology_page = '".$p."' ORDER BY topology_page";
		$resPath =& $pearDB->query($rqPath);
		$redirectPath =& $resPath->fetchRow();
		return $redirectPath;
	}
	
	$tab = getTopologyParent($p);
	$tabPath = array();
	$tabPath[$tab["topology_page"]] = array();
	$tabPath[$tab["topology_page"]]["name"] = $lang[$tab["topology_name"]];
	$tabPath[$tab["topology_page"]]["opt"] = $tab["topology_url_opt"];
	$tabPath[$tab["topology_page"]]["page"] = $tab["topology_page"];

	while($tab["topology_parent"]){
		$tab = getTopologyParent($tab["topology_parent"]);
		$tabPath[$tab["topology_page"]] = array();
		$tabPath[$tab["topology_page"]]["name"] = $lang[$tab["topology_name"]];
		$tabPath[$tab["topology_page"]]["opt"] = $tab["topology_url_opt"];
		$tabPath[$tab["topology_page"]]["page"] = $tab["topology_page"];
	}
	ksort($tabPath);

	$res =& $pearDB->query("SELECT * FROM topology WHERE topology_page = '".$p."'");
	$res->fetchInto($current);
	
	if ($current["topology_url_opt"])
		$req = "SELECT * FROM topology WHERE topology_url = '".$current["topology_url"]."' AND topology_url_opt = '".$current["topology_url_opt"]."' AND topology_page > '".$p."' ORDER BY topology_page ASC";
	else
		$req = "SELECT * FROM topology WHERE topology_url = '".$current["topology_url"]."' AND topology_url_opt is NULL AND topology_page > '".$p."' ORDER BY topology_page ASC";
	$res =& $pearDB->query($req);
	while ($res->fetchInto($new_url)){
		if (isset($lang[$new_url["topology_name"]]))
			$tabPath[$new_url["topology_page"]] = array();
			$tabPath[$new_url["topology_page"]]["name"] = $lang[$new_url["topology_name"]];
			$tabPath[$new_url["topology_page"]]["opt"] = $new_url["topology_url_opt"];
			$tabPath[$new_url["topology_page"]]["page"] = $new_url["topology_page"];
	}
	

	
	$tmp = array();
	foreach($tabPath as $k => $v){
		$ok = 0;
		foreach ($tmp as $key => $value)
			if ($value["name"] == $v["name"])
				$ok = 1;
		if ($ok == 0)
			$tmp[$k] = $v;
	}
	$tabPath = $tmp;
	
	$flag = '&nbsp;<img src="./img/icones/8x14/pathWayBlueStart.png" alt="" class="imgPathWay">&nbsp;';
	foreach ($tabPath as $cle => $valeur){
		echo $flag;
		?><a href="oreon.php?p=<? echo $cle.$valeur["opt"]; ?>" class="pathWay" ><? echo $valeur["name"]; ?></a><?
		$flag = '&nbsp;<img src="./img/icones/8x14/pathWayBlue.png" alt="" class="imgPathWay">&nbsp;';
	}

	if(isset($_GET["host_id"]))	{
		echo '&nbsp;<img src="./img/icones/8x14/pathWayBlue.png" alt="" class="imgPathWay">&nbsp;';
		echo getMyHostName($_GET["host_id"]);
	}
?>
<hr><br>