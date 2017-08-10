<?php

class Form {

	public static function isRequired($input = NULL){

        $args = func_get_args();

        if(count($input) > 1) {
            

            $returnResult = true;

            foreach($input as $input){

                if(!isset($input) || empty($input) || $input == NULL) {
                    $returnResult = false;
                }
                
                return $returnResult; 
            }
        }

        else {
    		if(isset($input) && !empty($input) || isset($input) && $input === "0") {
                return true;
            }
            else {
                return false;
            }
        }
	}

        public static function matches($input1, $input2){
                if($input1 == $input2) {
                       return true;
                }
                else {
                        return false;
                }
        }   
}

?>