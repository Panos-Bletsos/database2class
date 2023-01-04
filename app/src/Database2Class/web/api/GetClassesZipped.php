<?php

namespace Database2class\Database2class\Api;

use ZipArchive;

function create_zip($files = array(), $destination = '', $overwrite = false)
{
    //if the zip file already exists and overwrite is false, return false
    if (file_exists($destination) && !$overwrite) {
        return false;
    }
    //vars
    $valid_files = array();
    //if files were passed in...
    if (is_array($files)) {
        //cycle through each file
        foreach ($files as $file) {
            //make sure the file exists
            if (file_exists($file)) {
                $valid_files[] = $file;
            }
        }
    }
    //if we have good files...
    if (count($valid_files)) {
        //create the archive
        $zip = new ZipArchive();
        if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
            return false;
        }
        //add the files
        foreach ($valid_files as $file) {
            $zip->addFile($file, $file);
        }
        //debug
        //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

        //close the zip -- done!
        $zip->close();

        //check to make sure the file exists
        return file_exists($destination);
    } else {
        return false;
    }
}

function my_scandir($a_dir)
{
    $dir_contents = scandir($a_dir);
    $result = array();

    foreach ($dir_contents as $a_dir_content) {
        if (strcmp($a_dir_content, "..") === 0 || strcmp($a_dir_content, ".") === 0)
            continue;
        else
            array_push($result, $a_dir . $a_dir_content);
    }

    return $result;
}

function delete_files_in_output($files_to_delete)
{
    foreach ($files_to_delete as $a_file_to_delete) {
        if (!str_contains( $a_file_to_delete, 'Database.php') && !str_contains($a_file_to_delete, 'Settings.php'))
            unlink($a_file_to_delete);
    }
}

function download_output()
{
    $files_to_zip = my_scandir("/tmp/database2class/output/");

    array_push($files_to_zip, '../../Database.php');
    array_push($files_to_zip, '../../Settings.php');

    //print_r($files_to_include); exit();

    $filename = "/tmp/database2class/" . "db2PHPclass_" . date("Y_m_d_H_i_s", strtotime("now")) . ".zip";
    $result = create_zip($files_to_zip, $filename);

    delete_files_in_output($files_to_zip);

    if (file_exists($filename)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . basename($filename));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);

        //unlink($filename);

        exit;
    }
}

download_output();
?>