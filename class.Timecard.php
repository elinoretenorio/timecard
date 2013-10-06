<?php
/**
* Timecard class
*
* A simple timecard class that generates attendance related
* time calculations like total hours worked, late, overtime,
* undertime, payable hours, etc.
*
* @author  Elinore Tenorio
* @email   elinore.tenorio@gmail.com
*/

class Timecard {

    private $_time_in;
    private $_break_out;
    private $_break_in;
    private $_time_out;
	
	private $_required_work_minutes;
	private $_required_time_in;
	private $_break_allowance_minutes;
	private $_time_minutes;
	private $_time_hour;
	
    public function __construct($time_in, $break_out, $break_in, $time_out) {
		
		global $settings;
		
        $this->_time_in = strtotime($this->sanitizeTime($time_in));
        $this->_break_out = strtotime($this->sanitizeTime($break_out));
        $this->_break_in = strtotime($this->sanitizeTime($break_in));
        $this->_time_out = strtotime($this->sanitizeTime($time_out));
		
		$this->_required_time_minutes = $settings['COMPANY']['REQUIRED_TIME_MINUTES'];
		$this->_break_allowance_minutes = $settings['COMPANY']['BREAK_ALLOWANCE_MINUTES'];
		$this->_required_time_in = strtotime($settings['COMPANY']['REQUIRED_TIME_IN']);
		$this->_time_minutes = $settings['TIME']['MINUTES'];
		$this->_time_hour = $settings['TIME']['HOUR'];
		
		$this->_required_work_minutes = $this->_required_time_minutes + $this->_break_allowance_minutes;
	}
    
    public function getTardyMinutes() {
        $tardy_minutes = ($this->_time_in - $this->_required_time_in)/$this->_time_minutes;
        $tardy_minutes = ($tardy_minutes > 0) ? $tardy_minutes : 0;
        return $tardy_minutes ;
    }
    
    public function getTardyHours() {
        return $this->getHours($this->getTardyMinutes(), '%d hr %d min');
    }
    
    public function getExcessBreakMinutes() {
        $break_minutes = ($this->_break_in - $this->_break_out)/$this->_time_minutes;
        $break_minutes = ($break_minutes > $this->_break_allowance_minutes) ? ($break_minutes - $this->_break_allowance_minutes) : 0;
        return $break_minutes ;
    }
    
    public function getExcessBreakHours() {
    	return $this->getHours($this->getExcessBreakMinutes(), '%d hr %d min');
    }
    
    public function getGrossWorkMinutes() {
    	$worked_minutes = ($this->_time_out - $this->_time_in)/$this->_time_minutes;
        return $worked_minutes;
    }
    
    public function getGrossWorkHours() {
        return $this->getHours($this->getGrossWorkMinutes(), '%d hr %d min');
    }
	
	public function getNetWorkMinutes() {
    	$actual_worked_minutes = $this->getGrossWorkMinutes() - $this->_break_allowance_minutes - $this->getExcessBreakMinutes();
        $net_worked_minutes = ($actual_worked_minutes > $this->_required_time_minutes) ? $this->_required_time_minutes : $actual_worked_minutes;
        return $net_worked_minutes;
    }
    
    public function getNetWorkHours() {
        return $this->getHours($this->getNetWorkMinutes(), '%d hr %d min');
    }
	
	public function getUndertimeMinutes() {
    	$required_vs_worked_minutes = $this->_required_time_minutes - $this->getNetWorkMinutes();
        $undertime_minutes = ($required_vs_worked_minutes > 0) ? $required_vs_worked_minutes : 0;
        return $undertime_minutes;
    }
    
    public function getUndertimeHours() {
        return $this->getHours($this->getUndertimeMinutes(), '%d hr %d min'); 
    }
	
    public function getOvertimeMinutes() {
        $required_vs_worked_minutes = ($this->getGrossWorkMinutes() - $this->getNetWorkMinutes() - $this->_break_allowance_minutes);
        $overtime_minutes = ($required_vs_worked_minutes > 0) ? $required_vs_worked_minutes : 0;
        return $overtime_minutes;
    }
    
    public function getOvertimeHours() {
        return $this->getHours($this->getOvertimeMinutes(), '%d hr %d min');
    }
	
	private function sanitizeTime($time) {
		return filter_var($time, FILTER_SANITIZE_STRING);
	}
    
    /**
    * Convert minutes to hours
	*
    * @param integer @time Time represented in minutes
    * @return string @hours_and_minutes String representation of hours and minutes
    **/
    private function getHours($time, $format = '%d:%d') {
    	global $settings;
    	settype($time, 'integer');
    	if ($time < 1) {
        	return;
    	}
    	$hours = floor($time / $this->_time_hour);
    	$minutes = $time % $this->_time_minutes;
    	return sprintf($format, $hours, $minutes);
    }
	
	/**
    * Check if time is valid
	*
    * @param datetime @time Time represented in datetime format
    * @return boolean Returns true if time is valid
    **/
	public static function isValidTime($time) {
		if (preg_match('#^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$#', $time)) {
			return true;
		} 
		return false;
	}

}
