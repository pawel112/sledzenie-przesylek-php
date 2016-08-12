<?php
if (isset($_GET['numer']))
{
	require_once ('przesylka.php');

	$przesylka = new przesylka ($_GET['numer'], $_GET['operator']);
	$przesylka->wypisz_tabele();
}
else
{
	echo ('Numer przesyłki:<br/><form method="get" action="index.php"><input type="text" name="numer" /><select name="operator">
	<option value="dhl">DHL</option><option value="dpd">DPD</option><option value="ups">UPS</option><option value="fedex">Fedex</option><option value="kex">K-EX</option><option value="gls">GLS</option><option value="inpost">InPost</option><option value="crossborder">InPost Cross Border</option><option value="xpress">X-press</option><option value="poczta">Poczta Polska</option><option value="poczta,postnl">PostNL</option>
	</select><br/><input type="submit" value="Dalej" /></form>');
}
?>