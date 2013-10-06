<?php
/**
* Sample timecard
*
* Timecard generated from the attendance.csv file
*
* @author  Elinore Tenorio
* @email   elinore.tenorio@gmail.com
*/

date_default_timezone_set('Asia/Manila');
include 'class.Timecard.php';
$settings = parse_ini_file("settings.ini", true);

$row = 1;
$valid = true;

$filename = "attendance.csv";

if (($handle = fopen($filename, "r")) !== false) {
	echo "<table border=1>";
	echo "<tr>
			<td>ID</td>
			<td>Date</td>
			<td>Time In</td>
			<td>Break out</td>
			<td>Break in</td>
			<td>Time out</td>
			<td>&nbsp;</td>
			<td>Gross (min)</td>
			<td>Gross</td>
			<td>Lunch (mins)</td>
			<td>Lunch</td>
			<td>EB (min)</td>
			<td>EB</td>
			<td>Net (min)</td>
			<td>Net (hours)</td>
			<td>&nbsp;</td>
			<td>Tardy (min)</td>
			<td>Tardy</td>
			<td>UT (min)</td>
			<td>UT</td>
			<td>OT (min)</td>
			<td>OT</td>
		 </tr>";
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
    	
		$valid_in = Timecard::isValidTime($data[2]);
		$valid_break_out = Timecard::isValidTime($data[3]);
		$valid_break_in = Timecard::isValidTime($data[4]);
		$valid_out = Timecard::isValidTime($data[5]);
		
		if ($valid_in && $valid_break_out && $valid_break_in && $valid_out) {
		
			echo "<tr>";
			$num = count($data);
			$row++;
			for ($c=0; $c < $num; $c++) {
				echo "<td>" . $data[$c] . "</td>";
			}
			
			$time_in = $data[2];
			$break_out = $data[3];
			$break_in = $data[4];
			$time_out = $data[5];
			
			$timecard = new Timecard($time_in, $break_out, $break_in, $time_out);
			
			echo "<td>&nbsp;</td>";
			
			echo "<td>". $timecard->getGrossWorkMinutes() ."</td>";
			echo "<td>". $timecard->getGrossWorkHours() ."</td>";
			
			echo "<td>". $settings['COMPANY']['BREAK_ALLOWANCE_MINUTES'] ."</td>";
			echo "<td>". $settings['COMPANY']['BREAK_ALLOWANCE_HOURS'] ."</td>";
			
			echo "<td>". $timecard->getExcessBreakMinutes() ."</td>";
			echo "<td>". $timecard->getExcessBreakHours() ."</td>";
			
			echo "<td>". $timecard->getNetWorkMinutes() ."</td>";
			echo "<td>". $timecard->getNetWorkHours() ."</td>";
			
			echo "<td>&nbsp;</td>";
			
			echo "<td>". $timecard->getTardyMinutes() ."</td>";
			echo "<td>". $timecard->getTardyHours() ."</td>";
			
			echo "<td>". $timecard->getUndertimeMinutes() ."</td>";
			echo "<td>". $timecard->getUndertimeHours() ."</td>";
			
			echo "<td>". $timecard->getOvertimeMinutes() ."</td>";
			echo "<td>". $timecard->getOvertimeHours() ."</td>";
			
			echo "</tr>";
		
		} else {
		
			echo "<tr><td colspan='22'>Invalid Time format</td></tr>";
	
		}
		
    }
    
    echo "</table>";
    fclose($handle);
}
?>

