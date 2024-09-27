<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- debitor/pos_ordre_includes/boxCountMethods/cashBalance.php --- lap 4.1.1 - 2024.07.30 -
// LICENSE
//
// This program is free software. You can redistribute it and / or
// modify it under the terms of the GNU General Public License (GPL)
// which is published by The Free Software Foundation; either in version 2
// of this license or later version of your choice.
// However, respect the following:
//
// It is forbidden to use this program in competition with Saldi.DK ApS
// or other proprietor of the program without prior written agreement.
//
// The program is published with the hope that it will be beneficial,
// but WITHOUT ANY KIND OF CLAIM OR WARRANTY. See
// GNU General Public License for more details.
//
// Copyright (c) 2003-2024 Saldi.dk ApS
// ----------------------------------------------------------------------
// 20240729 PHR Various translations and currency adjustment.


function cashBalance ($kasse,$optalt,$godkendt,$cookievalue) {
	echo "<!-- function cashBalance Begin -->\n";

	global $bruger_id,$brugernavn;
	global $db,$db_encode,$FromCharset,$ToCharset;
#	global $printserver;
	global $regnaar,$reportNumber;
	global $tracelog;
	global $vis_saet;

	$dd=date("Y-m-d");
	$tid=date("H:i");
	if (!$reportNumber) $reportNumber = 0;
	$kortnavn = $optval = array();
	
	$qtxt = "select box6,box12 from grupper where art = 'POS' and kodenr = '2'";
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$div_kort_kto=$r['box6'];
	$vis_saet=trim($r['box12']);
	
	if (!$cookievalue) $cookievalue=if_isset($_COOKIE['saldi_kasseoptael'],NULL);
	$tmparray=explode(chr(9),$cookievalue);
	$ore_10       = if_isset($tmparray[0],0);
	$ore_20       = if_isset($tmparray[1],0);
	$ore_50       = if_isset($tmparray[2],0);
	$kr_1         = if_isset($tmparray[3],0);
	$kr_2         = if_isset($tmparray[4],0);
	$kr_5         = if_isset($tmparray[5],0);
	$kr_10        = if_isset($tmparray[6],0);
	$kr_20        = if_isset($tmparray[7],0);
	$kr_50        = if_isset($tmparray[8],0);
	$kr_100       = if_isset($tmparray[9],0);
	$kr_200       = if_isset($tmparray[10],0);
	$kr_500       = if_isset($tmparray[11],0);
	$kr_1000      = if_isset($tmparray[12],0);
	$kr_andet     = if_isset($tmparray[13],0);
#	$fiveRappen   = if_isset($tmparray[14],0);
#	$tenRappen    = if_isset($tmparray[15],0);
#	$twentyRappen = if_isset($tmparray[16],0);
	for ($x=15;$x<count($tmparray);$x++) {
		$optval[$x-15] = if_isset($tmparray[$x],0);
	}
	$qtxt = "select var_value from settings where var_name = 'change_cardvalue' limit 1";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$change_cardvalue = if_isset($r['var_value'],NULL);
	$qtxt = "select * from grupper where art = 'POS' and kodenr = '2'";
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$byttepenge=$r['box1'];
	$optalassist=$r['box2'];
	$printer_ip=explode(chr(9),$r['box3']);
	$printserver=strtolower($printer_ip[$kasse-1]);
	if (!$printserver)$printserver='localhost';
	if ($printserver=='box' || $printserver=='saldibox') {
		$filnavn="http://saldi.dk/kasse/".$_SERVER['REMOTE_ADDR'].".ip";
		if ($fp=fopen($filnavn,'r')) {
			$printserver=trim(fgets($fp));
			fclose ($fp);
			$printpopup=0;
		}
	} elseif (strtolower($printserver)=='popupbox') {
		$filnavn="http://saldi.dk/kasse/".$_SERVER['REMOTE_ADDR'].".ip";
		if ($fp=fopen($filnavn,'r')) {
			$printserver=trim(fgets($fp));
			fclose ($fp);
			$printpopup=1;
		}
	} else $printpopup=1;
	if (!$godkendt && $optalassist) kasseoptalling ($kasse,$optalt,$ore_10,$ore_20,$ore_50,$kr_1,$kr_2,$kr_5,$kr_10,$kr_20,$kr_50,$kr_100,$kr_200,$kr_500,$kr_1000,$kr_andet,$optval); #, $fiveRappen, $tenRappen, $twentyRappen
	$r=db_fetch_array(db_select("select * from grupper where art = 'RA' and kodenr = '$regnaar'",__FILE__ . " linje " . __LINE__));
	$startmd=$r['box1'];
	$startaar=$r['box2'];
	
	($startaar && $startmd)?$regnstart=$startaar."-".$startmd."-01":$regnstart='2000-01-01';

	if ($godkendt) {
		$qtxt = "insert into pos_events (ev_type,ev_time,cash_register_id,employee_id,order_id,file,line) ";
		$qtxt.= "values ";
		$qtxt.= "('13009','". date('U') ."','$kasse','$bruger_id','0','".__file__."','".__line__."')";
		db_modify ($qtxt,__FILE__ . " linje " . __LINE__);
		posbogfor($kasse,$regnstart,$reportNumber);
	}
	$optval=if_isset($_POST['optval']);
	$valuta=if_isset($_POST['valuta'],array());
	$ValutaKasseDiff=if_isset($_POST['ValutaKasseDiff']);
	$ValutaByttePenge=if_isset($_POST['ValutaByttePenge']);
	$ValutaTilgang=if_isset($_POST['ValutaTilgang']);
	$ValutaUdtages=if_isset($_POST['ValutaUdtages']);
	
// 	include("../includes/ConvertCharset.class.php");
	if ($db_encode=="UTF8") $FromCharset = "UTF-8";
	else $FromCharset = "iso-8859-15";
	$ToCharset = "cp865";
//	$convert = new ConvertCharset();
	$pfnavn="../temp/".$db."/kasseopg".str_replace("-","",$kasse).".txt";
	$fp=fopen("$pfnavn","w");
	$logfil="../temp/".$db."/kasseopg".str_replace("-","",$kasse).".log";
	$log=fopen("$logfil","a");
    setPrintHeaderTxt($FromCharset, $ToCharset, $fp, $dd, $tid, $kasse, $brugernavn);
	if ($optalassist) {
        setPrintTxt($fp, $log, $FromCharset, $ToCharset, $ore_10, $ore_20, $ore_50, $kr_1, $kr_2, $kr_5, $kr_10, $kr_20, $kr_50, $kr_100, $kr_200, $kr_500, $kr_1000, $kr_andet, $valuta, $optval, $change_cardvalue,$reportNumber);
	} else {
	 	include_once("pos_ordre_includes/boxCountMethods/findBoxSale.php");
		$svar=findBoxSale($kasse,$optalt,'DKK');
		$byttepenge=$svar[0];
		$tilgang=$svar[1];
		$diff=$svar[2];
		$kortantal = $svar[3];
		$kontkonto = explode(chr(9),$svar[4]);
		$kortnavn  = explode(chr(9),$svar[5]);
		$kortsum   = explode(chr(9),$svar[6]);
		$kontosum  = $svar[7];
#		$kontosalg = $svar[8];
		$omsatning = $tilgang+$kontosum;
		for ($x=0;$x<count($kortnavn);$x++) {
			$omsatning+= $kortsum[$x];
		}
		if ($godkendt) {
			$qtxt = "insert into report (date,type,description,count,total,report_number) ";
			$qtxt.= "values ('$dd','Turnover','Turnover, box $kasse','0','$omsatning','$reportNumber')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "insert into report (date,type,description,count,total,report_number) ";
			$qtxt.= "values ('$dd','Cash Amount','Change, box $kasse','0','$byttepenge','$reportNumber')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "insert into report (date,type,description,count,total,report_number) ";
			$qtxt.= "values ('$dd','Cash Approach','Approach, box $kasse','0','$tilgang','$reportNumber')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	}
		$txt1 = iconv($FromCharset, $ToCharset,'Dagens omsætning');
//		$txt1=$convert ->Convert('Dagens omsætning', $FromCharset, $ToCharset);
		$txt2=dkdecimal($omsatning,2);
		while (strlen($txt1)+strlen($txt2)<40) $txt2=" ".$txt2;
		fwrite($fp,"$txt1$txt2\n");
		fwrite($log,"$txt1$txt2\n");
		$txt1="Beholdning primo:";
		$txt2=dkdecimal($byttepenge,2);
		while (strlen($txt1)+strlen($txt2)<40) $txt2=" ".$txt2;
		fwrite($fp,"$txt1$txt2\n");
		fwrite($log,"$txt1$txt2\n");
		$txt1="Dagens indbetalinger:";
		$txt2=dkdecimal($tilgang,2);
		while (strlen($txt1)+strlen($txt2)<40) $txt2=" ".$txt2;
		fwrite($fp,"$txt1$txt2\n");
		fwrite($log,"$txt1$txt2\n");
		$txt1="Beholdning ultimo:";
		$txt2=dkdecimal($byttepenge+$tilgang,2);
		while (strlen($txt1)+strlen($txt2)<40) $txt2=" ".$txt2;
		fwrite($fp,"$txt1$txt2\n\n");
		fwrite($log,"$txt1$txt2\n\n");
	}
  setSignatureTxt($fp, $log);
	if ($kontosum) {
		if ($godkendt) {
			$qtxt = "insert into report (date,type,description,count,total,report_number) ";
			$qtxt.= "values ('$dd','Sale on account','Sale on account, box $kasse','0','$kontosum','$reportNumber')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		$txt1="Salg på konto";
		$txt1 = iconv($FromCharset, $ToCharset,$txt1);
//		$txt1=$convert ->Convert($txt1, $FromCharset, $ToCharset);
		$txt2=dkdecimal($kontosum,2);
		while (strlen($txt1)+strlen($txt2)<40) $txt2=" ".$txt2;
#			fwrite($fp,"\nSalg paa kort:\n");
		fwrite($fp,"$txt1$txt2\n");
		fwrite($log,"$txt1$txt2\n");
	}
	for ($x=0;$x<count($kortnavn);$x++) {
		if ($kortsum[$x]) {
			if ($godkendt) {
				$qtxt = "insert into report (date,type,description,count,total,report_number) ";
				$qtxt.= "values ('$dd','Payment method','$kortnavn[$x]','0','$kortsum[$x]','$reportNumber')";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			$txt1="$kortnavn[$x]";
#		$txt1 = iconv($FromCharset, $ToCharset,'Dagens omsætning');
//	$txt1=$convert ->Convert($txt1, $FromCharset, $ToCharset);
			$txt2=dkdecimal($kortsum[$x],2);
			while (strlen($txt1)+strlen($txt2)<40) $txt2=" ".$txt2;
#			fwrite($fp,"\nSalg paa kort:\n");
			if ($kortnavn[$x]) {
				if ($godkendt) {
					$qtxt = "insert into report (date,type,description,count,total,report_number) ";
					$qtxt.= "values ('$dd','Payment Card','$kortnavn[$x]','0','$kortsum[$x]','$reportNumber')";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				fwrite($fp,"$txt1$txt2\n");
				fwrite($log,"$txt1$txt2\n");
			}
		}
	}
	fwrite($fp,"\n\n\n");
	fwrite($log,"\n\n\n");

	fclose($fp);
	fclose($log);
	echo "<!-- function cashBalance End -->\n";
} # endfunc kassebeholdning
?>
