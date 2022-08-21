 <?php
 class gregorian2jalali
{
      // $mydate format must follow this format: 2007-02-12-1  (yyyy-mm-dd-D)
      // If $mydate is not set, current date will be returned.
      var $mydate = "";
      private $year;
      private $month;
      private $day;
      private $d;
      
      function Get_Date() {
          $year = date('Y');
          $month = date('m');
          $day = date('d');
          $d = date('w');
          return $year."-".$month."-".$day."-".$d;
      }
	  function div($a,$b) {
	  	return (int) ($a / $b);
	  }
	  function gregorian_to_jalali ($format="yyyy/mm/dd")
	  {
          $week= Array("&#1610;&#1603;&#1588;&#1606;&#1576;&#1607;","&#1583;&#1608;&#1588;&#1606;&#1576;&#1607;","&#1587;&#1607; &#1588;&#1606;&#1576;&#1607;","&#1670;&#1607;&#1575;&#1585;&#1588;&#1606;&#1576;&#1607;","&#1662;&#1606;&#1580;&#8204;&#1588;&#1606;&#1576;&#1607;","&#1580;&#1605;&#1593;&#1607;","&#1588;&#1606;&#1576;&#1607;");
          $months = Array("&#1601;&#1585;&#1608;&#1585;&#1583;&#1610;&#1606;","&#1575;&#1585;&#1583;&#1610;&#1576;&#1607;&#1588;&#1578;","&#1582;&#1585;&#1583;&#1575;&#1583;","&#1578;&#1610;&#1585;","&#1605;&#1585;&#1583;&#1575;&#1583;","&#1588;&#1607;&#1585;&#1610;&#1608;&#1585;","&#1605;&#1607;&#1585;","&#1570;&#1576;&#1575;&#1606;","&#1570;&#1584;&#1585;","&#1583;&#1610;","&#1576;&#1607;&#1605;&#1606;","&#1575;&#1587;&#1601;&#1606;&#1583;");

        if (($this->mydate)=="")
            $this->mydate = $this->Get_Date();

		$g_y = mb_substr($this->mydate, 0, 4);
		$g_m = mb_substr($this->mydate, 5, 2);
		$g_d = mb_substr($this->mydate, 8, 2);
		$d = mb_substr($this->mydate, 11, 1);


		$g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
		$gy = $g_y-1600;
		$gm = $g_m-1;
		$gd = $g_d-1;
		$g_day_no = 365*$gy+$this->div($gy+3,4)-$this->div($gy+99,100)+$this->div($gy+399,400);
		for ($i=0; $i < $gm; ++$i)
		$g_day_no += $g_days_in_month[$i];
		if ($gm>1 && (($gy%4==0 && $gy%100!=0) || ($gy%400==0)))
		/* leap and after Feb */
		$g_day_no++;
		$g_day_no += $gd;
		$j_day_no = $g_day_no-79;
		$j_np = $this->div($j_day_no, 12053); /* 12053 = 365*33 + 32/4 */
		$j_day_no = $j_day_no % 12053;
		$jy = 979+33*$j_np+4*$this->div($j_day_no,1461); /* 1461 = 365*4 + 4/4 */
		$j_day_no %= 1461;
		if ($j_day_no >= 366) {
		$jy += $this->div($j_day_no-1, 365);
		$j_day_no = ($j_day_no-1)%365;
		
		}
		for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i)
		$j_day_no -= $j_days_in_month[$i];
		$jm = $i+1;
		$jd = $j_day_no+1;
		$jy_s = mb_substr($jy, 2, 2);
		if ($jd<10)
			$jd = "0".$jd;
		
		switch ($format) {
		case "yy M dd D":
			return  ($week[$d]." ".$jd." ".$months[$jm-1]." ".$jy);
			break;
		case "yy/mm/dd":
			if ($jm<10)
				$jm = "0".$jm;
			return  ($jy_s."/".$jm."/".$jd);
			break;
		case "yyyy/mm/dd":
			if ($jm<10)
				$jm = "0".$jm;
			return  ($jy."/".$jm."/".$jd);
			break;
		case "dd":
			return  ($jd);
			break;
		case "mm":
			if ($jm<10)
				$jm = "0".$jm;
			return  ($jm);
			break;
		case "Y":
			return  ($jy);
			break;
		case "yyyy":
			return  ($jy);
			break;
		case "yy":
			return  ($jy_s);
			break;
		default:
		}
	}
}
?>