 
<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ----------debitor/rentalIncludes/newRtPeriod.php---lap 3.9.2-----2020-10-24-----
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
// but WITHOUT ANY KIND OF CLAIM OR WARRANTY.
// See GNU General Public License for more details.
//
// Copyright (c) 2003-2020 Saldi.dk ApS
// ----------------------------------------------------------------------


print "<form name = 'newRtPeriod' autocomplete='off' action='rental.php?thisRtId=$thisRtId&rtItemId=$rtItemId' method='post'>";
$qtxt="select kontonr,firmanavn  from adresser where lukket != 'on' order by firmanavn";
$x=0;
$q = db_select($qtxt,__FILE__ . " linje " . __LINE__); 
while ($r=db_fetch_array($q)) {
	$customer[$x]=$r['kontonr'] ." ".$r['firmanavn'];
	$x++;
}
print "<table>"; 
print "<tr><td>Kundenr</td><td><input type = 'text' name = 'newRtPeriodCustomer'  value = '$rtCustNo'></td></tr>";
print "<tr><td>Periode fra</td>";
print "<td><input type = 'text' name = 'newRtFrom' value = '". date('d-m-Y',$newRtPeriodFrom) ."'></td></tr>";
print "<tr><td>Periode til</td>";
print "<td><input type = 'text' name = 'newRtTo' value = '". date('d-m-Y',$newRtPeriodTo) ."'></td></tr>";
print "<tr><td collspan = '2'><button type='submit'>Opret</button></td></tr>";
print "</table>";
print "</form>";
exit;
?>
</body></html>
