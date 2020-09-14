<?php
    // 3. Exercise 2 - PHP Scripting

        // 3.1 - running from command console
    $hs = "localhost";
    $us = "root";
    $ps = "";

    $db = "raintreeassessment";
    $conn = mysqli_Connect($hs, $us, $ps, $db);

    if($conn === false){
        die("database is not connected");
    }
    else {
        echo("database is connected"), "\n";
    }

        // 3.2 - Required Functionalilities

            // 3.2 a) Displaying required coloumns
    $sql_1 = "SELECT *\n"

    . "FROM patient\n"

    . "INNER JOIN insurance\n"

    . "ON patient._id = insurance.patient_id\n"

    . "ORDER BY insurance.from_date, patient.last";

    $result = mysqli_query($conn, $sql_1);

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            echo $row["pn"].", ". $row["last"]. ", " . $row["first"].", ". $row["iname"].", ".date("m-d-y", strtotime($row["from_date"])).", ".date("m-d-y", strtotime($row["to_date"]))."\n";
        }
    } 
    else {
    echo "0 results";
    }

            // 3.2 b) Displaying statistics
    echo "\nStatistics ordered alphabetically:\n";

    $sql_2 = "SELECT CONCAT(first, \" \", last) AS name\n"

    . "FROM patient";

    $result = mysqli_query($conn, $sql_2);
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            echo $row["name"]."\n";

            $str = strtoupper(str_replace(" ", "", $row["name"]));
            $lenOfStr = strlen($str);
            $stringParts = str_split($str);
            sort($stringParts);
            $str = implode('', $stringParts); 
            foreach (count_chars($str, 1) as $strr => $value) {    
                $inPercent = ($value/$lenOfStr)*100;
                echo chr($strr) ."\t". "$value"."\t". round($inPercent, 2)."%"."\n";
            }
        }
    } 
    else {
    echo "0 results";
    }

    // 4. Exercise 3 - Object Oriented PHP

        // 4.1 - Creating PHP Interface
    interface PatientRecord { 
        public  function getId(); 
        public  function getPn(); 
    }

        // 4.2  Creating PHP Class - "Patient"
    class Patient implements PatientRecord{
        public $first;
        public $last;
        public $dob;
        private $insurances = array();
        public $_id;
        public $pn;

        function __construct($first, $last, $dob) {
            static $_id = 0;
            $this->_id = ++$_id;
            $this->pn = "000000000{$_id}";
            $this->first = $first; 
            $this->last = $last;
            $this->dob = $dob; 
        }

        function getId() {                               // get patient _id property
            return $this->_id;
        }

        function getPn() {                              // get patient pn property
            return $this->pn;
        }

        function getFirstLast(){                        // get patient name(First Last) property
            return $this->first ." ". $this->last;
        }

        public function addInsurance( $insuranceObject){  
            $this->insurances[] = $insuranceObject;
        }

        public function returnInsuranceArray(){         // get patient insurance array property
            return $this->insurances;
        }

        public function displyingTableData($dateToBeChecked){    // get insurance validity

            foreach ($this->insurances as $element) { 
                $contractDateBegin = date('Y-m-d', strtotime($element->from_date));
                $contractDateEnd = date('Y-m-d', strtotime($element->to_date));
                if (($dateToBeChecked >= $contractDateBegin) && ($dateToBeChecked <= $contractDateEnd)){
                    $isValid = "Yes";
                }else{
                    $isValid = "No";
                }
                echo $this->pn. ", ". $this->first." ". $this->last.", ". $element->iname. ", " . $isValid. "\n";  
            } 
        }
    }

        // 4.3  Creating PHP Class - "Insurance"
    class Insurance implements PatientRecord{
        public $iname;
        public $from_date;
        public $to_date;
        public $patient;

        function __construct($iname, $from_date, $to_date, $patient) {
            static $_id = 0;
            $this->_id = ++$_id;
            $this->iname = $iname; 
            $this->from_date = $from_date;
            $this->to_date = $to_date; 
            $this->patient = $patient;
        }

        function getId() {                               // get insurance _id
            return $this->_id;
        }

        function getPn() {                               // get patient no.
            return $this->patient->getPn() ;
        }

        function checkInsuranceValidity($dateToBeChecked){      // return insurance validity as true or false
            if ($this->to_date == null){
                return "effective infinitely";
            }
            $dateToBeChecked=date('Y-m-d', strtotime($dateToBeChecked));
            $contractDateBegin = date('Y-m-d', strtotime($this->from_date));
            $contractDateEnd = date('Y-m-d', strtotime($this->to_date));
            
            if (($dateToBeChecked >= $contractDateBegin) && ($dateToBeChecked <= $contractDateEnd)){
                return "true";
            }else{
                return "false";
            }
        }
    }

        // 4.4  displaying all patients and insurances with its validity from today's date ordered by patient no.

    echo "\nTest Script To check validity of insurances comparing from today's date ordered by patient no.\n";

    function helperMethod($patientObj) {
        $today = date('Y-m-d ');
        $today=date('Y-m-d', strtotime($today));                
        $patientObj->displyingTableData($today);
        return;
    }
    
    $sql = "SELECT *\n"

    . "FROM patient\n"

    . "INNER JOIN insurance\n"

    . "ON patient._id = insurance.patient_id\n"

    . "ORDER BY patient.pn";
    
    $result = mysqli_query($conn, $sql);
    
    $prevPatientId = 0;
    while($row = mysqli_fetch_assoc($result)) {
        if($row["patient_id"] != $prevPatientId){
            if ($prevPatientId !=0){
                helperMethod($patient);
            }
            $patient = new Patient($row["first"], $row["last"], $row["dob"]);
            $prevPatientId = $row["patient_id"];
        }
        $insurance = new Insurance($row["iname"], $row["from_date"], $row["to_date"], $patient);
        $patient->addInsurance($insurance);
    }
    helperMethod($patient);

        // 4.4 test script to check features of classes
    function checkPatientClass(){
        $response = "";
        echo "Type Patient's\n";
        $first = readline('Firstname: ');
        $last = readline('Lastname: ');
        $dob = readline('DOB(eg: YYYY/MM/DD) : '); 
        $patient = new Patient($first, $last, $dob);
        while($response != "exit"){
            echo "Type 'id' to get id or 'pn' to get patient no. or 'name' to get Name or  'insurance' to create insurance object or 'exit' to exit without quotes \n";
            $response = readline();
            if ($response == "exit"){
                return;
            }
            else if ($response == "id"){
                $ans = $patient->getId();
                echo "$ans\n";
            }
            else if ($response == "pn"){
                $ans = $patient->getPn();
                echo "$ans\n";
            }
            else if ($response == "name"){
                $ans = $patient->getFirstLast();
                echo "$ans\n";
            }
            else if ($response == "insurance"){
                
                checkInsuranceClass($patient);
            break;
            }
            else{
                echo "invalid input";
                continue;
            }
        }
        return;
    }

    function checkInsuranceClass($patient){
        $response = "";
        echo "Type insurance's: \n";
        $iname = readline('Iname: ');
        $from_date = readline('from_date(eg: YYYY/MM/DD): ');
        $to_date = readline('to_date(eg: YYYY/MM/DD): ');
        $insurance = new Insurance($iname, $from_date, $to_date, $patient);
        while($response != "exit"){
            echo "Type 'id' to get id or 'pn' to get patient no. or 'isvalid' to check validity for insurance or 'patient' to create new patient or 'insurance' to create new insurance' or 'exit' to exit without quotes  \n";
            $response = readline();
            if ($response == "exit"){
                return;
            }
            else if ($response == "id"){
                $ans = $insurance->getId();
                echo "$ans\n";
            }
            else if ($response == "pn"){
                $ans = $insurance->getPn();
                echo "$ans\n";
            }
            else if ($response == "isvalid"){
                $response = readline("Type date (eg : YYYY/MM/DD): ");
                echo $insurance->checkInsuranceValidity($response), "\n";
            }
            else if ($response == "patient"){
                checkPatientClass();
                return;
            }
            else if ($response=="insurance"){

            }
            else{
                echo "invalid input";
                continue;
            }
        }
    }

    function testScript(){
        echo "\nTo start the script type 'start' without quotes  or type 'exit' or enter to exit\n";
        $response = "";
        $response = readline();
        if ($response == "start"){
            echo "\nRunning test script (lets create patient object)\n";
            while(true == true){        
                checkPatientClass();
                break;
            }
        }
        else{
            return;
        }
    }

    testScript()
    
?>