<?php

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
