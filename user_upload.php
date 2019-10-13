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

        $SQL = "INSERT INTO users (name, surname, email) VALUES (:name, :surname, :email);";

        $stmt = $conn->prepare($SQL);
        $stmt->execute($data);

        echo "The details ".$data['name']." ".$data['surname']." ".$data['email']." were entered successfully into the database.\n";

    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage()."\n"; 
        die();
    }
}

// Function to check if the database configs have all been passed.
function checkDatabaseConfigs ($dbConfig){
    
    $errors = '';

    if(!array_key_exists("u", $dbConfig)){
        $errors .= "Please add the MySQL username. USAGE : -u <mysqlusername>\n";
    }

    if(!array_key_exists("p", $dbConfig)){
        $errors .= "Please add the MySQL password. USAGE : -u <mysqlpassword>\n";
    }

    if(!array_key_exists("h", $dbConfig)){
        $errors .= "Please add the MySQL host. USAGE : -u <mysqlhost>\n";
    }

    if(!array_key_exists("d", $dbConfig)){
        $errors .= "Please add the MySQL database. USAGE : -u <mysqldatabase>\n";
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


$file = getopt(null, ["file:"]);
$dbConfig = getopt("u:p:h:d:");

$mysqlUsername = (isset($dbConfig['u']) ? $dbConfig['u'] : '');
$mysqlPassword = (isset($dbConfig['p']) ? $dbConfig['p'] : '');
$mysqlHost = (isset($dbConfig['h']) ? $dbConfig['h'] : '');
$mysqlDBName = (isset($dbConfig['d']) ? $dbConfig['d'] : '');

// execute this peace of code if the --help directive is passed
if(in_array('--help', $argv)){

    echo "**************************************\n";
    echo "*               USAGE                *\n";
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
if(in_array('--create_table', $argv)){

    $checkDatabaseConfigs = checkDatabaseConfigs ($dbConfig);
    if(!empty($checkDatabaseConfigs)){
        echo $checkDatabaseConfigs;
        return;
    }
    createUsersTable($mysqlUsername, $mysqlPassword, $mysqlHost, $mysqlDBName);

    return;
    
}

if(in_array('--dry_run', $argv)){

    $checkFile = checkFile ($file['file']);

    if(!empty($checkFile)){
        echo $checkFile;
        return;
    }
    try {
        $delimiter = ',';
        if (($handle = fopen($file['file'], 'r')) !== FALSE){

            // Headrow
            $headers = fgetcsv($handle, 4096, ';', '"');

            //Rows
            while (($row = fgetcsv($handle, 4096, $delimiter)) !== FALSE){

                //print_r($row);

                $name = ucfirst(strtolower(trim($row[0])));
                $surname = ucfirst(strtolower(trim($row[1])));
                $email = strtolower(trim($row[2]));
                echo $name." ".$surname." ";

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo $email." is an invalid email format\n";
                }else{
                    echo $email."\n";
                }               

            }
            fclose($handle);
        }
    } catch (Exception $e) {
        echo 'Failed to read the file: ' . $e->getMessage()."\n"; 
        die();
    }
}
