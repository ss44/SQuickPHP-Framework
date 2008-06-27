<?php
/**
 * A convience class to help with testing.
 */

class Tester{
	
	public $tests = array();
	
	public function addTest($expected = null, $result = null){
		$pair = array();
		$pair['expected'] = $expected;
		$pair['result'] = $result;
		$this->tests[] = $pair;
	}
	
	public function run(){
		$failed = 0;
		echo "\nRunning Tester\n";
		
		foreach ($this->tests as $case=>$test){
			if ($test['expected'] !== $test['result']){
				echo "Failed Test " . ($case + 1) . "\n";
				var_dump($test['expected']);
				var_dump($test['result']);
				$failed++; 
			}
		}
		
		if ($failed > 0){
			echo "Failed $failed tests\n\n";
		}else{
			echo "Passed ". count($this->tests) ." tests\n\n";
		}
	}
}
?>