<?php

class FileUploader
{
    private $fileName;
    private $file;
    private $type;
    private $tempName;
    private $extension;
    private $newFileName;
    const FILE_SIZE = 2000000;

    public function __construct($file, $directory, $newFileName)
    {
        //var_dump($file);
        $this->file = $file;
        $this->processFile();
        $this->testFile();
        $this->moveFile($directory, $newFileName);
    }

    private function processFile()
    {
        $f = $this->file;
        $this->fileName = $f['name'];
        $this->type = $f['type'];
        $this->tempName = $f['tmp_name'];
        $tempArr = explode('.', $f['name']);
        $this->extension = end($tempArr);
    }

    private function testFile(){
        $f = $this->file;
        $ferr = $this->file['error'];
        if(!isset($ferr) || is_array($ferr)){
            throw new RuntimeException("No File Uploaded.");
        }

        switch($ferr){
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
                break;
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException("Exceeded Filesize Limit.");
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException("No File Sent.");
            default:
                throw new RuntimeException("Unknown Errors.");
        }

        if($f['size'] > self::FILE_SIZE){
            throw new RuntimeException('Exceeded Filesize Limit.');
        }

        $goodExts = array('txt', 'csv');
        $goodTypes = array('text/plain','text/csv', 'application/vnd.ms-excel');

        if(in_array($this->extension, $goodExts) === false || in_array($this->type, $goodTypes) === false){
            throw new RuntimeException("This page only accepts .txt and .csv files. Please upload the correct format.");
        }

    }

    private function moveFile($directory, $newFileName){
        if(!move_uploaded_file($this->tempName, "$directory/$this->fileName")){
            throw new RuntimeException("Unable to move file to /KewaFiles directory");
        }

        $today = new DateTime('now');
        $dateFormat = $today->format('m-d-y-H-i-s');
        $newName = "$directory/$newFileName-$dateFormat.$this->extension";
        if(!rename("$directory/$this->fileName", $newName)){
            throw new RuntimeException("Unable to Rename File $this->fileName to: $newName");
        }
        $this->newFileName = $newName;

    }

    public function getNewFileName(){
        return $this->newFileName;
    }
}

?>