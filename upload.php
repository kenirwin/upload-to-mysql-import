<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define ('UPLOAD_FOLDER',''); //absolute path to web-writable folder path - no trailing slash define ('FIELD_SEP',"\t"); define ('LINE_SEP',"\n"); define ('DB_TABLE',''); define ('DB_HOST',''); define ('DB_NAME',''); define ('DB_USER',''); define ('DB_PASS','');


if (isset($_POST['filesubmit'])) {
    HandleUpload();
}
else {
    DisplayForm();
}

function DisplayForm () {
    print <<<FORM
<form method="post" enctype="multipart/form-data"> <input name="userfile" type="file" accept=".txt"> <input type="submit" name="filesubmit" value="Import" id="import"> </form> FORM; }
 
function HandleUpload () {              
    if ($_FILES['userfile']['name'] == '' && $_FILES['userfile']['size'] == 0) { 
        DisplayForm();
        die('<p>File not uploaded</p>');
    }
    $fileName = $_FILES['userfile']['name'];
    $tmpName  = $_FILES['userfile']['tmp_name'];
    $fileSize = $_FILES['userfile']['size'];
    $fileType = $_FILES['userfile']['type'];
    $fileLocation = UPLOAD_FOLDER.'/'.$fileName;
    
    if (! move_uploaded_file($tmpName, $fileLocation)) { //if fail to mv
        print "<p class=warning>failed to move file: check to be sure web server has write permissions to the tmp/ directory</p>";
        
    }
    try 
    {
        $pdo = new PDO(
            "mysql:host=".DB_HOST.";dbname=".DB_NAME,
            DB_USER,
            DB_PASS,
            array
            (
                PDO::MYSQL_ATTR_LOCAL_INFILE => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            )
        );
    } 

    catch (PDOException $e) 
    {
        die("database connection failed: ".$e->getMessage());
    }
    
    try {
        $affectedRows = $pdo->exec
                      (
                          "LOAD DATA LOCAL INFILE "
                          .$pdo->quote($fileLocation)
                          ." INTO TABLE `".DB_TABLE."` FIELDS TERMINATED BY "
                          .$pdo->quote(FIELD_SEP)
                          ."LINES TERMINATED BY "
                          .$pdo->quote(LINE_SEP)
                      );
        echo "Loaded a total of $affectedRows records from this file.\n";
    } catch (Exception $e) {
        var_dump($e);
    }
}
?>
