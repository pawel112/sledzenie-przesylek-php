<?php
const API_FURGONETKA = "http://test.furgonetka.pl";

class przesylka
{
	private $nr_przesylki;
	private $operator;
	private $losy;
	private $telefon_mail;
	
	public function pobierz_dane_telemailowe ($nr_przesylki, $operator)
	{
		if ($operator == 'poczta')
		{
			return array("8:00 - 20:00","+48 801 333 444","+48 438 420 600", "uslugi.pocztowe@poczta-polska.pl");
		}
		elseif ($operator == "inpost")
		{
			return array("7:00 - 21:00","+48 801 400 100", "+48 722 444 000", "bok@inpost.pl");
		}
		elseif ($operator == "crossborder")
		{
			return array("7:00 - 21:00","+48 801 400 100", "+48 722 444 000", "bok@inpost.pl");
		}
		elseif ($operator == "dpd")
		{
			return array ("8:00 - 20:00, soboty 8:00 - 14:00", "+48 801 400 373, +48 22 577 55 55", "cc@dpd.com.pl");
		}
		elseif ($operator == "dhl")
		{
			return array ("7:00 - 20:00, soboty 8:00 - 16:00", "+48 42 6 345 345", "kontakt.pl@dhl.com");
		}
		elseif ($operator == "ups")
		{
			return array ("0:00 - 23:59, soboty 0:00 - 23:59, niedziele 0:00 = 23:59", "+48 22 534 00 00", "custservplpl@ups.com");
		}
		elseif ($operator == "fedex")
		{
			return array ("8:00 - 18:00", "+48 801 002 800, +48 22 211 80 00", "http://www.fedex.com/pl/customer/writefedex.html");
		}
		elseif ($operator == "kex")
		{
			return array ("brak danych", "+48 801 702 505, +48 22 21 22 800", "biuro@k-ex.pl");
		}
		elseif ($operator == "gls")
		{
			return array ("7:00 - 19:00, soboty 7:00 - 15:00", "+48 804 262 262, +48 46 814 82 20", "office@gls-poland.com");
		}
		elseif ($operator == "xpress")
		{
			return array ("brak danych", "+48 801 00 66 00, +48 22 493 33 33", "waw@x-press.com.pl");
		}
		elseif (in_array("poczta", $this->operator) == true)
		{
			return array("8:00 - 20:00","+48 801 333 444","+48 438 420 600", "uslugi.pocztowe@poczta-polska.pl");
		}
		else
		{
			return NULL;
		}
	}
	
	public function pobierz_dane ()
	{
		
		//pobieram dane przesyłek
		if (($this->operator == "dhl") || ($this->operator == "dpd") || ($this->operator == "ups") || ($this->operator == "fedex") || ($this->operator == "kex") || ($this->operator == "gls") || ($this->operator == "inpost") || ($this->operator == "crossborder") || ($this->operator == "xpress") || ($this->operator == "poczta") || ((is_array ($this->operator)) && (in_array("poczta", $this->operator))))
		{
			if ((is_array ($this->operator)) && (in_array("poczta", $this->operator)))
			{
				$operator = "poczta";
			}
			else
			{
				$operator = $this->operator;
			}
			
			
			$url = API_FURGONETKA."/api/getTracking.xml?package_no=".$this->nr_przesylki."&service=".$operator;
			$xml = simplexml_load_file($url);
			$this->losy = array();
			
			for ($i=0; $i<count ($xml->tracking->node); $i++)
			{
				array_push ($this->losy, array ((string) $xml->tracking->node[$i]->station, (string) $xml->tracking->node[$i]->date, (string) $xml->tracking->node[$i]->time, (string) $xml->tracking->node[$i]->description));
			}
		}
		
		if ((is_array ($this->operator)) && (in_array("postnl", $this->operator)))
		{
			$url = 'http://www.postnl.post/details/';
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, 1);
			curl_setopt($ch,CURLOPT_POSTFIELDS, 'barcodes='.$this->nr_przesylki);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
			$wynik = trim(curl_exec($ch));
			curl_close($ch);
			$wynik = str_replace(array("\r\n", "\n", "\r"), '', $wynik);
			$wynik = str_replace(array("  ","													","		","		","	"), '', $wynik);
			preg_match('@<td class="date".+</td></tr></tbody><tfoot><tr>@iu', $wynik, $wynik);
			
			if (empty($wynik) == true)
			{
				return;
			}
			
			$wynik = str_replace ("POLAND", "", $wynik);
			$wynik = ereg_replace ('<td class="date" data-order="[a-zA-Z0-9_]+">','<br/>',$wynik[0]);
			$wynik = ereg_replace ('</td></tr><tr class="detail"><td class="date" data-order="[a-zA-Z0-9_]+" style="white-space: nowrap;">','<br/>',$wynik);
			$wynik = str_replace (array("</td><td>","</td><td class=\"country\" rowspan=\"6\">", "</td><td class=\"country\">","</td></tr></tbody><tfoot><tr>","<td class=\"date\" data-order=\"[0-9]+\">"),"<br/>",$wynik);
			$wynik = substr($wynik, 5, -5);
			$wynik = str_replace ("<br/><br/>", "<br/>", $wynik);
			$wynik = explode ("<br/>", $wynik);
			
			for ($i=0; $i<count ($wynik)/2; $i++)
			{
				array_push ($this->losy, array ( null, substr (substr ($wynik[2*$i], 0, 10), 6, 9).'-'.substr (substr ($wynik[2*$i], 0, 10), 3, 2).'-'.substr (substr ($wynik[2*$i], 0, 10), 0, 2), substr($wynik[2*$i].":00", 11), $wynik[2*$i+1]));
			}
			
			//sortowanie
			function compare_lastname($a, $b)
			{
				return strnatcmp($b['1'], $a['1']);
			}

			usort($this->losy, 'compare_lastname');
		}
	}
	
	public function __construct ($nr_przesylki, $operator)
	{ 
		$this->nr_przesylki = trim ($nr_przesylki);
		$this->operator = trim ($operator);
		
		if ($this->operator)
		{
			if (strpos ($this->operator, ',') !== false)
			{
				$this->operator = explode(",", $this->operator);
			}
		}
		$this->pobierz_dane();
		$this->telefon_mail = $this->pobierz_dane_telemailowe ($nr_przesylki, $this->operator);
    }
	
	public function wypisz_tabele ()
	{
		?>
		<html>
			<head>
				<link rel="stylesheet" type="text/css" href="css.css">
			</head>
			<body>
				<div class="datagrid"><table>
					<thead>
						<tr>
							<th>Przesyłka</th><th><?php echo ($this->nr_przesylki); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Firma doręczająca:</td><td><?php echo ($this->zwroc_nazwe_operatora()); ?></td>
						</tr>
						<tr class="alt">
							<td>Godziny pracy infolinii:</td><td>poniedziałki-piątki <?php echo ($this->telefon_mail[0]); ?></td>
						</tr>
						<tr>
							<td>Telefon:</td><td><?php echo ($this->telefon_mail[1]); if (count($this->telefon_mail == 4)) { echo (', '.$this->telefon_mail[2]); } ?></td>
						</tr>
						<tr class="alt">
							<td>Mail:</td><td><?php echo ($this->telefon_mail[count($this->telefon_mail)-1]); ?></td>
						</tr>
					</tbody>
					</table>
				</div>
				<br/>
				<br/>
				<div class="datagrid"><table>
					<thead>
						<tr>
							<th>Zdarzenie</th><th>Data</th><th>Godzina</th><th>Miejsce</th>
						</tr>
					</thead>
					<tbody>
						<?php
						for ($i=0; $i<13; $i++)
						{
							if ($i%2==0)
							{
								?>
								<tr>
									<td><?php echo ($this->losy[$i][3]); ?></td><td><?php echo ($this->losy[$i][0]); ?></td><td><?php echo ($this->losy[$i][1]); ?></td><td><?php echo ($this->losy[$i][2]); ?></td>
								</tr>
								<?php
							}
							else
							{
								?>
								<tr class="alt">
									<td><?php echo ($this->losy[$i][3]); ?></td><td><?php echo ($this->losy[$i][0]); ?></td><td><?php echo ($this->losy[$i][1]); ?></td><td><?php echo ($this->losy[$i][2]); ?></td>
								</tr>
								<?php
							}
						}
						?>
					</tbody>
					</table>
				</div>
			</body>
		</html>
		<?php
	}
	
	public function zwroc_nazwe_operatora ()
	{
		if ($this->operator == 'poczta')
		{
			return "Poczta Polska";
		}
		elseif ($this->operator == "inpost")
		{
			return "InPost";
		}
		elseif ($this->operator == "crossborder")
		{
			return "InPost";
		}
		elseif ($this->operator == "dpd")
		{
			return "DPD";
		}
		elseif ($this->operator == "dhl")
		{
			return "DHL";
		}
		elseif ($this->operator == "ups")
		{
			return "UPS";
		}
		elseif ($this->operator == "fedex")
		{
			return "Fedex";
		}
		elseif ($this->operator == "kex")
		{
			return "K-EX";
		}
		elseif ($this->operator == "gls")
		{
			return "GLS";
		}
		elseif ($this->operator == "xpress")
		{
			return "X-press";
		}
		elseif (in_array("poczta", $this->operator) == true)
		{
			return "Poczta Polska";
		}
		else
		{
			return NULL;
		}
	}
}
?>