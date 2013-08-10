<?php

if(isset($_GET['gender']) && $_GET['gender'] === 'male' || $_GET['gender'] === 'men' || $_GET['gender'] === 'man'){
	$genders = 'men';
} else if(isset($_GET['gender']) && $_GET['gender'] === 'female' || $_GET['gender'] === 'women'){
	$genders = 'women';
} else {
	// Genders
	$genders = array("men", "women");
	// Select Gender
	$genders = $genders[rand(0,1)];
}

// Append zeros to numbers
function convNum($number) {
    if (strlen($number) == 1) {
        return "00" . $number;
    }
    else if (strlen($number) == 2) {
        return "0" . $number;
    } else {
        return $number;
    }
};
// Count the number of photos in the gender directory
function countPhotos($g){
	if ($handle = opendir('../portraits/'.$g)) {
        while (($file = readdir($handle)) !== false) {
            $ex = explode(".", $file);
            if (!in_array($file, array('.', '..')) && !is_dir($file) && $ex[(count($ex)-1)] == "jpg") { 
                $count++;
            }
        }
    }
    return $count;
};

// open the file in a binary mode
$name = '../portraits/'.$genders.'/'.convNum(rand(0, countPhotos($genders))).'.jpg';
$fp = fopen($name, 'rb');

// send the right headers
header("Content-Type: image/png");
header("Content-Length: " . filesize($name));

// dump the picture and stop the script
fpassthru($fp);
exit;