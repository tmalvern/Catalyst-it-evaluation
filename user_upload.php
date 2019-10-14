<?php

// Function to create the users database table.
function createUsersTable($dbuser, $dbpass, $dbhost, $dbname){

    try{
        $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);

        $SQL = "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(45) NOT NULL,
            `surname` VARCHAR(45) NOT NULL,
            `email` VARCHAR(45) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE INDEX `email_UNIQUE` (`email` ASC));";

        $conn->query($SQL);

        echo "The table users was created successfully in the database ".$dbname." \n";

    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage()."\n"; 
        die();
    }
}

// Function to insert into the users database table.
function insertUsersTable($dbuser, $dbpass, $dbhost, $dbname, $data){

    try{
        $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);

        $SQL = "INSERT INTO `users` (`name`, `surname`, `email`) VALUES (:name, :surname, :email);";

        $stmt = $conn->prepare($SQL);
        $stmt->execute($data);

        echo "The details ".$data['name']." ".$data['surname']." ".$data['email']." were entered successfully into the database.\n";

    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage()."\n"; 
        die();
    }
}
// Function to check if the email address is already in the database.
function emailExists($dbuser, $dbpass, $dbhost, $dbname, $email) {

    try{
        $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);

        $stmt = $conn->prepare("SELECT `email` FROM `users` WHERE `email`=?");
        $stmt->execute([$email]); 

        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage()."\n"; 
        die();
    }
}

// Function to check if the database configs have all been passed.
function checkDatabaseConfigs ($dbuser, $dbpass, $dbhost, $dbname){
    
    $errors = '';

    if(empty($dbuser)){
        $errors .= "Please add the MySQL username. USAGE : -u <mysqlusername>\n";
    }

    if(empty($dbpass)){
        $errors .= "Please add the MySQL password. USAGE : -p <mysqlpassword>\n";
    }

    if(empty($dbhost)){
        $errors .= "Please add the MySQL host. USAGE : -h <mysqlhost>\n";
    }

    if(empty($dbname)){
        $errors .= "Please add the MySQL database. USAGE : -d <mysqldatabase>\n";
    }
        
    return $errors;
    
}

// Function to check if the csv has been passed and if it has been passed, does it exist. Also checks if the file that has been passed is a CSV.
function checkFile ($file){
    
    $errors = '';
    $fileInfo = pathinfo($file);

    if(empty($file)){
        $errors .= "Please add the file : --file <csvfile>\n";
    }elseif(!is_readable($file)){

        $errors .= "The file ".$file." does not exist or it is not readable. Please check the file\n";

    }elseif($fileInfo['extension'] !== 'csv'){
        $errors .= "Please add a csv file\n";
    }
        
    return $errors;    
}
// read in the CSV file 
function readInFile($file){
    try {
        $delimiter = ',';
        if (($handle = fopen($file, 'r')) !== FALSE){

            // Headrow
            $headers = fgetcsv($handle, 4096, $delimiter, '"');
            $data = [];
            
            //Rows
            $i=0;
            while (($row = fgetcsv($handle, 4096, $delimiter)) !== FALSE){

                $data[$i]['name'] = ucfirst(strtolower(trim($row[0])));
                $data[$i]['surname'] = ucfirst(strtolower(trim($row[1])));
                $data[$i]['email'] = strtolower(trim($row[2]));

                $i++;
            }
            fclose($handle);
        }
    } catch (Exception $e) {
        echo 'Failed to read the file: ' . $e->getMessage()."\n"; 
        die();
    }

    return $data;
}

// Check if an email address is valid

function isValidEmail($email){

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    return true;
}

$params = getopt("u:p:h:d:", ["file:", "dry_run", "help", "create_table"]);

//print_r($params);
//die;
$mysqlUsername = (isset($params['u']) ? trim($params['u']) : '');
$mysqlPassword = (isset($params['p']) ? trim($params['p']) : '');
$mysqlHost = (isset($params['h']) ? trim($params['h']) : '');
$mysqlDBName = (isset($params['d']) ? trim($params['d']) : '');

$file = (isset($params['file']) ? $params['file'] : '');

// execute this peace of code if the --help directive is passed
if(array_key_exists('help', $params) || count($params) == 0){

    echo "**************************************\n";
    echo "*        Available options           *\n";
    echo "**************************************\n\n\n";

    echo "--file [csv file name] – this is the name of the CSV to be parsed\n";
    echo "--create_table – this will cause the MySQL users table to be built (and no further action will be taken)\n"; 
    echo "--dry_run – this will be used with the --file directive but not insert into the DB. \n"; 
    echo "-u – MySQL username\n"; 
    echo "-p – MySQL password\n";
    echo "-h – MySQL host\n";
    echo "-d - MySQL database\n";
    echo "--help – which will output all the  of directives of their usage\n"; 

    return;
}

// execute this peace of code if the --create_table directive is passed
if(array_key_exists('create_table', $params)){

    $checkDatabaseConfigs = checkDatabaseConfigs ($mysqlUsername, $mysqlPassword, $mysqlHost, $mysqlDBName);
    if(!empty($checkDatabaseConfigs)){
        echo $checkDatabaseConfigs;
        return;
    }
    createUsersTable($mysqlUsername, $mysqlPassword, $mysqlHost, $mysqlDBName);

    return;
    
}

if(array_key_exists('dry_run', $params)){

    $checkFile = checkFile ($file);

    if(!empty($checkFile)){
        echo $checkFile;
        return;
    }

    $data = readInFile($file);

    echo "Found data in the file ".$file."\n";

    foreach ($data as $value) {
        
        echo "Name:".$value['name']." Surname:".$value['surname']." Email:".$value['email'].(!isValidEmail($value['email']) ? '. The email address is not valid' : '')."\n";
    }
    
}

if(!empty($file) && !array_key_exists('dry_run', $params)){

    $checkDatabaseConfigs = checkDatabaseConfigs ($mysqlUsername, $mysqlPassword, $mysqlHost, $mysqlDBName);
    if(!empty($checkDatabaseConfigs)){
        echo $checkDatabaseConfigs;
        return;
    }
    createUsersTable($mysqlUsername, $mysqlPassword, $mysqlHost, $mysqlDBName);

    $data = readInFile($file);
    
    foreach ($data as $value) { 

        if(isValidEmail($value['email'])){

            if(emailExists($mysqlUsername, $mysqlPassword, $mysqlHost, $mysqlDBName, addslashes($value['email']))){

                echo "The email address ". $value['email']. " already exist in the database.\n";
                continue;
            }
            $finalData['name'] = addslashes($value['name']);
            $finalData['surname'] = addslashes($value['surname']);
            $finalData['email'] = addslashes($value['email']);
            insertUsersTable($mysqlUsername, $mysqlPassword, $mysqlHost, $mysqlDBName, $finalData);
        }else{
            echo "Name:".$value['name']." Surname:".$value['surname']." Email:".$value['email']." This email address is not valid, so the row will not be saved.\n";
        }
    }
}