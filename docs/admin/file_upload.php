<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
require_once('../../admin-includes/admin.security_ajax.inc.php');

header('Content-type: application/json');

$valid_exts = array('jpeg', 'jpg', 'png', 'gif', 'doc', 'docx', 'pdf'); // valid extensions
$max_size = 1600 * 1024; // max file size (1600kb)
$path = '../media/'; // default upload directory

if (isset($_POST['folder-path']) && substr($_POST['folder-path'], 0, 9) == '../media/') {
	$path = $_POST['folder-path'];
} else {
	$status = 'Bad request in folder path!';
	echo json_encode(array('status' => $status));
	exit();
}


if ( $_SERVER['REQUEST_METHOD'] === 'POST' )
{
  if( @is_uploaded_file($_FILES['image']['tmp_name']) )
  {
    // get uploaded file extension
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    // looking for format and size validity
    if (in_array($ext, $valid_exts) AND $_FILES['image']['size'] < $max_size)
    {
      // unique file path
      //$path = $path . uniqid(). '.' .$ext;
	  $path = $path . $_FILES['image']['name'];
      // move uploaded file from temp to uploads directory
      if (move_uploaded_file($_FILES['image']['tmp_name'], $path))
      {
        $status = 'Image successfully uploaded!';
      }
      else {
        $status = 'Upload Fail: Unknown error occurred!';
      }
    }
    else {
      $status = 'Upload Fail: Unsupported file format or It is too large to upload!';
    }
  }
  else {
    $status = 'Upload Fail: File not uploaded!';
  }
}
else {
  $status = 'Bad request!';
}

// echo out json encoded status
echo json_encode(array('status' => $status));
?> 