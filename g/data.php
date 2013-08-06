<?php
// Location of male and female pictures
define("MALE_PORTRAITS", "portraits/men");
define("FEMALE_PORTRAITS", "portraits/women");

// All of the data that will be used to generate random user profiles
require('names.php');
require('domains.php');

// Choose a specific value from a field
function getSpecific($field, $key, $gender = null, $email = null) {
	global $male_first_names, $female_first_names, $last_names, $email_domains;

	if ($gender === null && in_array($field, array("first", "picture"))) {
		trigger_error("Must supply gender for first name/picture!", E_USER_ERROR);
	}

	if (!is_numeric($gender) || ($gender < 0 || $gender > 1)) {
		$gender = mt_rand(0, 1);
	}

	if ($field == "first") {
		// Female
		if ($gender) {
			return $female_first_names[$key];
		} else {
			return $male_first_names[$key];
		}
	}
	else if ($field == "last") {
		return $last_names[$key];
	}
	else if ($field == "email") {
		return $email[0] . $email[1] . $email[2] . "@" . $email_domains[$key];
	}
	else if ($field == "picture") {
		if ($gender) {
			return "http://randomuser.me/g/" . FEMALE_PORTRAITS . "/" . convNum($key) . ".jpg";
		} else {
			return "http://randomuser.me/g/" . MALE_PORTRAITS . "/" . convNum($key) . ".jpg";
		}
	} else {
		return null;
	}
}

// Chooses a random item from the supplied field.
function getRandom($field, $gender = null) {
	global $male_first_names, $female_first_names, $last_names, $email_domains;

	if ($gender === null && in_array($field, array("first", "picture"))) {
		trigger_error("Must supply gender for first name/picture!", E_USER_ERROR);
	}

	if (!is_numeric($gender) || ($gender < 0 || $gender > 1)) {
		$gender = mt_rand(0, 1);
	}

	// First name field
	if ($field == "first") {
		// Male = 0 Female = 1
		if ($gender) {
			$key = array_rand($female_first_names);
			return array($female_first_names[$key], $key);
		} else {
			$key = array_rand($male_first_names);
			return array($male_first_names[$key], $key);
		}
	}

	// Last name
	else if ($field == "last") {
		$key = array_rand($last_names);
		return array($last_names[$key], $key);
	}

	// Email w/ random 2 digit number
	else if ($field == "email") {
		$key = array_rand($email_domains);
		$rand = mt_rand(10, 99);
		return array($rand . "@" . $email_domains[$key], $key, $rand);
	}

	// Profile picture determined by gender
	else if ($field == "picture") {
		if ($gender) {
			$img = mt_rand(0, female_pics());
			return array("http://randomuser.me/g/" . FEMALE_PORTRAITS . "/" . convNum($img) . ".jpg", $img);
		} else {
			$img = mt_rand(0, male_pics());
			return array("http://randomuser.me/g/" . MALE_PORTRAITS . "/" . convNum($img) . ".jpg", $img);
		}
	} else {
		return array(null, 0);
	}
}

function genProfile($gender = null, $seed = null) {
	global $male_first_names, $female_first_names, $last_names, $email_domains;

	// If gender isn't supplied, choose a random gender
	if ($gender === null) {
		$gender = mt_rand(0,1);
	} else {
		// Male
		if ((is_numeric($gender) && $gender == 0) || strtolower($gender) == "male") {
			$gender = 0;
		}
		// Female
		else if (((is_numeric($gender) && $gender == 1) || strtolower($gender) == "female")) {
			$gender = 1;
		// Choose a random gender if user misspelled or used invalid number
		} else {
			$gender = mt_rand(0,1);
		}
	}

	// If no seed is provided, randomly generate the data
	if ($seed === null) {
		$first = getRandom("first", $gender);
		$last = getRandom("last");
		$email_rand = getRandom("email");
		$email = $first[0] . $last[0] . $email_rand[0];
		$picture = getRandom("picture", $gender);

		$json = array(
				"user" => array(
					"gender" => gender($gender),
					"name" => array(
						"first" => ucfirst($first[0]),
						"last" => ucfirst($last[0])
						),
					"email" => $email,
					"picture" => $picture[0],
					"seed" => seedGen($gender, $first, $last, $email_rand, $picture)
					)
		);
		return json_encode($json);
	} else {
		// If seed is less than the 18 minimum requirement or is not numeric, generate new seed based off of supplied seed
		if ((strlen($seed) < 18 || strlen($seed) > 18) || !is_numeric($seed)) {
			mt_srand(ascii(md5($seed)));
			for ($a = 1; $a <= 6; $a++) {

				// 1Gender [0,1]
				if ($a == 1) {
					$min = 0;
					$max = 1;
				}

				// 2First_name [0, varies]
				else if ($a == 2) {
					$min = 0;
					if ($gender) {
						$max = (count($female_first_names)-1);
					} else {
						$max = (count($male_first_names)-1);
					}
				}

				// 3Last_name [0, varies]
				else if ($a == 3) {
					$min = 0;
					$max = (count($last_names)-1);
				}

				// 4Email [0, varies]
				else if ($a == 4) {
					$min = 0;
					$max = (count($email_domains)-1);
				}

				// 5Email (2 random digits) [10, 99]
				else if ($a == 5) {
					$min = 10;
					$max = 99;
				}
				
				// 6Picture [0, varies]
				else if ($a == 6) {
					$min = 0;
					if ($gender) {
						$max = female_pics();
					} else {
						$max = male_pics();
					}
				}
				$new_seed .= convNum(mt_rand($min, $max));

				// Remember Gender
				if ($a == 1) {
					$gender = $new_seed;
				}
			}
		// Else, verify that the supplied seed is a valid seed and correct each section accordingly
		} else {
			$seed_ex = seedData($seed);

			// Seed for random numbers if there are fixes
			mt_srand($seed);

			// 1Gender
			if ($seed_ex[0] > 1 || $seed_ex[0] < 0) {
				$seed_ex[0] = mt_rand(0, 1);
			}

			// 2First_name
			// Female
			if ($gender) {
				if ($seed_ex[1] > (count($female_first_names)-1)) {
					$seed_ex[1] = mt_rand(0, (count($female_first_names)-1));
				}
			// Male
			} else {
				if ($seed_ex[1] > (count($male_first_names)-1)) {
					$seed_ex[1] = mt_rand(0, (count($male_first_names)-1));
				}
			}

			// 3Last_name
			if ($seed_ex[2] > (count($last_names)-1)) {
				$seed_ex[2] = mt_rand(0, (count($last_names)-1));
			}

			// 4Email
			if ($seed_ex[3] > (count($email_domains)-1)) {
				$seed_ex[3] = mt_rand(0, (count($email_domains)-1));
			}

			// 5Email (2 random digits)
			if ($seed_ex[4] < 10 && $seed_ex[4] > 99) {
				$seed_ex[4] = mt_rand(10, 99);
			}
			
			// 6Picture [0, varies]
			if ($gender) {
				if ($seed_ex[5] > female_pics()) {
					$seed_ex[5] = mt_rand(0, female_pics());
				}
			// Male
			} else {
				if ($seed_ex[5] > male_pics()) {
					$seed_ex[5] = mt_rand(0, male_pics());
				}
			}
			foreach ($seed_ex as &$val) {
				$val = convNum($val);
			}

			$new_seed = implode($seed_ex);
		}

		// If there are were no fixes made to the seed, make new_seed = original seed
		if ($new_seed == null) {
			$new_seed = $seed;
		}

		// Split new_seed into seed info
		$seedinfo = seedDataBack($new_seed);

		$first = getSpecific("first", $seedinfo[1], $seedinfo[0]);
		$last = getSpecific("last", $seedinfo[2]);
		$email = getSpecific("email", $seedinfo[3], $seedinfo[0], array($first, $last, $seedinfo[4]));
		$picture = getSpecific("picture", $seedinfo[5], $seedinfo[0]);
		$json = array(
				"user" => array(
					"gender" => gender($seedinfo[0]),
					"name" => array(
						"first" => ucfirst($first),
						"last" => ucfirst($last)
						),
					"email" => $email,
					"picture" => $picture,
					"seed" => $seed
					)
		);
		if ($seed !== null && isset($_GET['gender'])) {
			$json["info"] = "Please note that gender was determined based on the seed provided and not from the gender supplied.";
		}
		return json_encode($json);

	}
}

function convNum($number) {
	if (strlen($number) == 1) {
		return "00" . $number;
	}
	else if (strlen($number) == 2) {
		return "0" . $number;
	} else {
		return $number;
	}
}

function gender($gender) {
	if ($gender == 1) {
		return "female";
	} else {
		return "male";
	}
}

function seedGen($gender, $first, $last, $email, $picture) {
	return convNum($gender) . convNum($first[1]) . convNum($last[1]) . convNum($email[1]) . convNum($email[2]) . convNum($picture[1]);
}

function seedData($seed) {
	$seed = str_split($seed, 3);
	return $seed;
}

function seedDataBack($seed) {
	$seed = str_split($seed, 3);
	foreach ($seed as &$val) {
		if ($val != "000") {
			$val = ltrim($val, "0");
		} else {
			$val = 0;
		}
	}
	return $seed;
}

function ascii($text) {
      $array = str_split($text);
      foreach($array as $char) {
            $encoding += ord($char);
      }
      return $encoding;
}

function male_pics() {
	if ($handle = opendir(MALE_PORTRAITS)) {
		while (($file = readdir($handle)) !== false) {
			$ex = explode(".", $file);
			if (!in_array($file, array('.', '..')) && !is_dir($file) && $ex[(count($ex)-1)] == "jpg") { 
				$male_portraits++;
			}
		}
	}
	return --$male_portraits;
}

function female_pics() {
	if ($handle = opendir(FEMALE_PORTRAITS)) {
		while (($file = readdir($handle)) !== false) {
			$ex = explode(".", $file);
			if (!in_array($file, array('.', '..')) && !is_dir($file) && $ex[(count($ex)-1)] == "jpg") { 
				$female_portraits++;
			}
		}
	}
	return --$female_portraits;
}
?>