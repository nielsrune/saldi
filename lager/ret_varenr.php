<?php
// ----------lager/ret_varenr.php-------------patch 3.0.7 -- 20101018----------
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg
//
// Dette program er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
//
// En dansk oversaettelse af licensen kan laeses her:
// http://www.fundanemt.com/gpl_da.html
//
// Copyright (c) 2004-2010 DANOSOFT ApS
// ----------------------------------------------------------------------
@session_start();
$s_id=session_id();

$title="Ret varenummer";
$modulnr=9;
$css="../css/standard.css";

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");

if (isset($_GET['id'])) $id = $_GET['id'];
elseif(isset($_POST['id'])) {
	$id = $_POST['id'];
	$varenr = $_POST['varenr'];
	$nyt_varenr = addslashes(trim($_POST['nyt_varenr']));
}

print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>\n";
print "<tr><td height = \"25\" align=\"center\" valign=\"top\">\n";
print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\"><tbody>\n";
print "<tr><td width=\"10%\" $top_bund><a href=varekort.php?id=$id accesskey=L>Luk</a></td>";
print "<td width=\"80%\" $top_bund align=\"center\">$title</td>";
print "<td width=\"10%\" $top_bund align=\"right\"><br></td></tr>";
print "</tbody></table>";
print "</td></tr>\n";
print "<tr><td>\n";
print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" width=100% valign = \"center\" align = \"center\"><tbody>\n";

if (($nyt_varenr)&&('$nyt_varenr'!='$varenr')) {
	if ($r=db_fetch_array(db_select("select id from varer where varenr = '$nyt_varenr'"))) {
		print "<BODY onLoad=\"javascript:alert('Varenummer: $nyt_varenr er i brug, varenummer ikke &aelig;ndret')\">";
	}
	else {
		db_modify("update varer set varenr='$nyt_varenr' where id='$id'");
		$x=0;
		$q=db_select("select ordrelinjer.id as ordrelinje_id, ordrer.art as art, ordrer.ordrenr as ordrenr from ordrelinjer, ordrer where ordrer.status<3 and ordrelinjer.ordre_id = ordrer.id and ordrelinjer.vare_id = '$id'");
		while ($r=db_fetch_array($q)) {
			$x++;
			db_modify("update ordrelinjer set varenr='$nyt_varenr' where id='$r[ordrelinje_id]'");
			if ($x==1) echo "<tr><td>Varenummer rettet i f&oslash;lgende ordrer: $r[ordrenr]";
			else echo ", $r[ordrenr]";
		}
		if ($x>=1)echo "</td></tr><tr><td><hr></td></tr>";
		print "<BODY onLoad=\"javascript:alert('Varenummer er rettet fra $varenr til $nyt_varenr')\">";
		print "<meta http-equiv=\"refresh\" content=\"0;URL=varekort.php?id=$id\">";

	}
}

if ($r=db_fetch_array(db_select("select varenr from varer where id = '$id'"))) $varenr=$r['varenr'];

print "<form name=ret_varenr action=ret_varenr.php method=post>"
;
print "<tr><td align=center>Varenummer rettes i alle uafsluttede ordrer, tilbud, indk&oslash;bsforslag og indk&oslash;bsordrer</td></tr>";
print "<tr><td align=center>Bem&aelig;rk at hvis der er brugere som er ved at redigere en ordre kan dette bevirke at varenummeret ikke &aelig;ndres</td></tr>";
print "<tr><td align=center>i den p&aring;g&aelig;ldende ordre. Det anbefales derfor at tilse at &oslash;vrige brugere lukker alle ordrevinduer.</td></tr>";
print "<tr><td align=center>&AElig;ndring af varenummer har ingen indflydelse p&aring; varestatestik eller andet, bortset fra at varen vil figurere</td></tr>";
print "<tr><td align=center>med det gamle varenummer i ordrer som er afsluttet f&oslash;r &aelig;ndringsdatoen.</td></tr>";

print "<tr><td align=center><hr width=50%></td></tr>";
print "<tr><td align=center>Ret varenummer $varenr til: <input type=text name=nyt_varenr  width=30 value=\"$varenr\"></td></tr>";
print "<input type=hidden name=id  width=30 value='$id'>";
print "<input type=hidden name=varenr  width=30 value=\"$varenr\">";
print "<tr><td align=center><input type=submit value=\"Ret\" name=\"submit\"></td></tr>";
print "</form>";

print "</tbody></table";
print "</td></tr>\n";
print "</tbody></table";




?>