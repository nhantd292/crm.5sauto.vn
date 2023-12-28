<?php
namespace ZendX\File;

class Upload extends \Zend\File\Transfer\Adapter\Http{
	
	public function uploadFile($fileInput, $uploadDirectory, $options = null, $prefix = 'x_'){
		if($options == null) {
			$this->setDestination($uploadDirectory, $fileInput);
			$fileName	= pathinfo($this->getFileName($fileInput), PATHINFO_BASENAME);
		}
		
		if($options['task'] == 'rename') {
			$fileExtension	= pathinfo($this->getFileName($fileInput), PATHINFO_EXTENSION);
			$fileName		= $prefix . $this->randomString(10) .'.'. $fileExtension;
			
			if(!empty($options['file_name'])) {
			    $fileName = $options['file_name'] .'.'. $fileExtension;
			}
			
			$this->addFilter('Rename',
			    array(
    				'target'	=> $uploadDirectory . $fileName,
    				'overwrite'	=> true,
			    ), 
			    $fileInput
			);
		}
		
		$this->receive($fileInput);
		
		return $fileName;
	}
	
	public function removeFile($fileName, $fileDirectory, $options = null) {
	    @unlink($fileDirectory . $fileName);
	}
	
	private function randomString($length = 5){
	
		$arrCharacter = array_merge(range('a','z'), range(0,99));
		$arrCharacter = implode($arrCharacter, '');
		$arrCharacter = str_shuffle($arrCharacter);
	
		$result		= substr($arrCharacter, 0, $length);
		return $result;
	}
}