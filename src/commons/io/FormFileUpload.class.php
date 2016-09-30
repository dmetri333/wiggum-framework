<?php
namespace wiggum\commons\io;

use \wiggum\commons\util\File;

class FormFileUpload {
	
    private $fileArray;
    private $uploadDir;
    private $uploaded;

    /**
     * Wrapper to perform a file upload from a form
     *
     * @param array $fileArray - the $_FILES[inputname] array for a single file
     * @param String $uploadDir - the destination directory to upload file to
     */
    function __construct(array $fileArray, $uploadDir) {
        $this->fileArray = $fileArray;
        $this->uploadDir = $uploadDir;
        $this->uploaded = array();
    }
    
    /**
     * the uploaded file should have been uploaded to a temp dir
     * move the file to the target directory
     * 
     * @return boolean
     */
    public function doUpload() {
        $result = false;
        
        if(is_array($this->fileArray['tmp_name'])) {
        	error_log('file array is in multi upload format, use doMultiUpload()');
        	return false;
        }
        
        $errno = $this->fileArray['error']; 
		if($errno == UPLOAD_ERR_OK) {

			if(!File::createFolder($this->uploadDir,true)) {
	        	error_log('Failed to create directory ' . $this->uploadDir);
	        	return false;
	        }
			
			if(is_uploaded_file($this->fileArray['tmp_name'])) {
				$result = move_uploaded_file($this->fileArray['tmp_name'], $this->uploadDir.$this->fileArray['name']);
			} else {
				$result = copy($this->fileArray['tmp_name'], $this->uploadDir.$this->fileArray['name']);
			}
			
			if($result) {
				$this->uploaded[] = $this->fileArray;
			
				//can't rely on browser to determine mime type, so do our own check.
				$this->setMimeType($this->uploadDir.$this->fileArray['name']);
			}
		} else {
			error_log($this->getErrMsg($errno));    
			$result = false;
		}
        
        return $result;
    }
    
    /**
     * 
     * Enter description here ...
     * @return boolean
     */
    public function doMultiUpload() {
		if(!File::createFolder($this->uploadDir,true)) {
        	error_log('Failed to create directory ' . $this->uploadDir);
        	return false;
        }
    	
    	$numFiles = count($this->fileArray['name']);
    	$numUploaded = 0;
    	for($i=0; $i<$numFiles; $i++) {
    		$errno = $this->fileArray['error'][$i];
    		if ($errno == UPLOAD_ERR_OK) {
    			
    			if(is_uploaded_file($this->fileArray['tmp_name'][$i])) {
					$result = move_uploaded_file($this->fileArray['tmp_name'][$i], $this->uploadDir.$this->fileArray['name'][$i]);
				} else {
					$result = copy($this->fileArray['tmp_name'][$i], $this->uploadDir.$this->fileArray['name'][$i]);
				}
				
				if($result) {
					$this->uploaded[] = array(
						'tmp_name' => $this->fileArray['tmp_name'][$i],
						'name' => $this->fileArray['name'][$i],
						'type' => $this->fileArray['type'][$i],
						'size' => $this->fileArray['size'][$i],
						'error' => $this->fileArray['error'][$i]
					);
					
					//can't rely on browser to determine mime type, so do our own check.
					$this->setMimeType($this->uploadDir.$this->fileArray['name'][$i], $numUploaded);
					
					$numUploaded++;
				}
    			
    		} else {
    			error_log($this->getErrMsg($errno));
    		}
    		
    	}
    	return ($numUploaded > 0) ? true : false;
    }
    
    /**
     * get a description for a given error code
     * these are predefined php errors when uploading a file from a post form
     * 
     * @param int $errno - error code from a file upload
     * @return string - description of error
     */
    private function getErrMsg($errno) {
        if($errno == UPLOAD_ERR_INI_SIZE) {
            return 'uploaded file larger than max size specified in php.ini';
        } else if($errno == UPLOAD_ERR_FORM_SIZE) {
            return 'uploaded file larger than max size specified in form field'; 
        } else if($errno == UPLOAD_ERR_PARTIAL) {
            return 'server only received part of the uploaded file';
        } else if($errno == UPLOAD_ERR_NO_FILE) {
            return 'the http request did not contain the uploaded file';
        } else if($errno == UPLOAD_ERR_NO_TMP_DIR) {
            return 'the server does not have a temp upload directory';
        } else if($errno == UPLOAD_ERR_CANT_WRITE) {
            return 'the uploaded file could not be written to disk';
        } else {
            return 'an error occured during file upload. errno: ' . $errno;
        }
    }
    
	/**
	 * 
	 * Enter description here ...
	 * @param integer $index
	 */
    public function getFileName($index = 0) {
    	if(count($this->uploaded) <= $index) { 
    		error_log('index out of bounds');
    		return false; 
    	}
    	return $this->uploaded[$index]['name'];
    }
    
    /**
     * 
     * Enter description here ...
     * @param integer $index
     */
    public function getErrorCode($index = 0) {
    	if(count($this->uploaded) <= $index) { 
    		error_log('index out of bounds');
    		return false; 
    	}
    	return $this->uploaded[$index]['error'];
    }
    
    /**
     * 
     * Enter description here ...
     * @param integer $index
     */
    public function getTempFileName($index = 0) {
		if(count($this->uploaded) <= $index) { 
    		error_log('index out of bounds');
    		return false; 
    	}
    	return $this->uploaded[$index]['tmp_name'];
    }
    
    /**
     * 
     * Enter description here ...
     * @param integer $index
     */
    public function getFileType($index = 0) {
    	if(count($this->uploaded) <= $index) { 
    		error_log('index out of bounds');
    		return false; 
    	}
    	return $this->uploaded[$index]['type'];
    }
    
    /**
     * 
     * Enter description here ...
     * @param integer $index
     */
    public function getFileSize($index = 0) {
    	if(count($this->uploaded) <= $index) { 
    		error_log('index out of bounds');
    		return false; 
    	}
    	return $this->uploaded[$index]['size'];
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function getUploaded() {
    	return $this->uploaded;
    }
    
    /**
     * 
     * Enter description here ...
     * @param integer $index
     */
	public function getFileExt($index = 0) {
		if(count($this->uploaded) <= $index) {
    		error_log('index out of bounds');
    		return false; 
    	}
		return File::getExt($this->uploaded[$index]['name']);
	}
	
	/**
	 * Determine the mime type of the uploaded file.
	 * The browser provides a mime type when the file is uploaded, but it is not a reliable source.
	 *
	 * @param string $file - the full path to the file
	 * @param integer $index
	 */
	private function setMimeType($file, $index = 0) {
		if(count($this->uploaded) <= $index) {
    		error_log('index out of bounds');
    		return false; 
    	}
		
		if(function_exists('finfo_open')) {
			//use Fileinfo extension
			
			//use Fileinfo extension
			//$finfo = finfo_open(FILEINFO_MIME, MAGIC_FILE);
			
			//use this for newer versions of php 5.3
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			
			if(!$finfo) {
  				error_log('finfo can\'t read mime db');
  			} else {
   				$info = finfo_file($finfo, $file);
   				finfo_close($finfo);
   				//magic db might provide extra information after mime type, so strip that out
   				$typeInfo = explode(';', $info);
   				$this->uploaded[$index]['type'] = $typeInfo[0];
   				
   				return $typeInfo[0];
  			}
		}
		
		return false;
	}
	
}
?>