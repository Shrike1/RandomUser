<?php
require_once 'global.php';

// Location of male and female pictures
define("MALE_PORTRAITS", "portraits/men");
define("FEMALE_PORTRAITS", "portraits/women");

// Choose a specific value from a field
function getSpecific($field, $id, $gender = null, $domain = null) {
    if ($gender == null && in_array($field, array("first_name", "picture"))) {
        error("Gender value is required");
    }

    if (!is_numeric($gender) || ($gender < 1 || $gender > 2)) {
        $gender = mt_rand(1, 2);
    }

    if ($field == "first_name") {
        return getFirstName($id);
    }
    else if ($field == "last_name") {
        return getLastName($id);
    }
    else if ($field == "domain") {
        return $domain[0] . $domain[1] . $domain[2] . "@" . getDomain($id);
    }
    else if ($field == "picture") {
        if ($gender == 1) {
            return "http://randomuser.me/g/" . MALE_PORTRAITS . "/" . convNum($id) . ".jpg";
        }
        else if ($gender == 2) {
            return "http://randomuser.me/g/" . FEMALE_PORTRAITS . "/" . convNum($id) . ".jpg";
        } else {
            error("Supplied gender value is invalid");
        }
    } else {
        error("Invalid field specified");
    }
}

// Chooses a random item from the supplied field.
function getRandom($field, $gender = null) {
    if ($gender == null && in_array($field, array("first_name", "picture"))) {
        error("Gender value is required");
    }

    if (!is_numeric($gender) || ($gender < 1 || $gender > 2)) {
        $gender = mt_rand(1, 2);
    }

    // First name field
    if ($field == "first_name") {
        // Male = 1 Female = 2
        if ($gender == 1) {
            $id = getRandomFirstName(1);
        }
        else if ($gender == 2) {
            $id = getRandomFirstName(2);
        } else {
            error("Supplied gender value is invalid");
        }
        return array(getFirstName($id), $id);
    }

    // Last name
    else if ($field == "last_name") {
        $id = getRandomLastName();
        return array(getLastName($id), $id);
    }

    // Email w/ random 2 digit number
    else if ($field == "domain") {
        $id = getRandomDomain();
        $rand = mt_rand(10, 99);
        return array($rand . "@" . getDomain($id), $id, $rand);
    }

    // Profile picture determined by gender
    else if ($field == "picture") {
        // Male
        if ($gender == 1) {
            $img = mt_rand(0, male_pics());
            return array("http://randomuser.me/g/" . MALE_PORTRAITS . "/" . convNum($img) . ".jpg", $img);
        }
        else if ($gender == 2) {
            $img = mt_rand(0, female_pics());
            return array("http://randomuser.me/g/" . FEMALE_PORTRAITS . "/" . convNum($img) . ".jpg", $img);
        } else {
            error("Supplied gender value is invalid");
            return 0;
        }
    } else {
        error("Invalid field specified");
    }
}

function genProfile($gender = null, $seed = null) {
    // If gender isn't supplied, choose a random gender
    if ($gender == null) {
        $gender = mt_rand(1, 2);
    } else {
        // Male
        if ((is_numeric($gender) && $gender == 1) || strtolower($gender) == "male") {
            $gender = 1;
        }
        // Female
        else if (((is_numeric($gender) && $gender == 2) || strtolower($gender) == "female")) {
            $gender = 2;
        // Choose a random gender if user misspelled or used invalid number
        } else {
            $gender = mt_rand(1, 2);
        }
    }

    // If no seed is provided, randomly generate the data
    if ($seed === null) {
        $first = getRandom("first_name", $gender);
        $last = getRandom("last_name");
        $domain_rand = getRandom("domain");
        $domain = $first[0] . $last[0] . $domain_rand[0];
        $picture = getRandom("picture", $gender);

        $json = array(
            "user" => array(
                "gender" => gender($gender),
                "name" => array(
                    "first" => ucfirst($first[0]),
                    "last" => ucfirst($last[0])
                ),
                "email" => $domain,
                "picture" => $picture[0],
                "seed" => seedGen($gender, $first, $last, $domain_rand, $picture)
            )
        );
        return json_encode($json);

    } else {
        // If seed is less than the 18 minimum requirement or is not numeric, generate new seed based off of supplied seed
            if ((strlen($seed) < 18 || strlen($seed) > 18) || !is_numeric($seed)) {
                $seeder = ascii(md5($seed));
                mt_srand($seeder);
                for ($a = 1; $a <= 6; $a++) {
                    // Gender
                    if ($a == 1) {
                        $gender = mt_rand(1, 2);
                        $new_seed .= convNum($gender);
                    }

                    // First Name
                    else if ($a == 2) {
                        $new_seed .= convNum(getRandomFirstName($gender, $seeder));
                    }

                    // Last Name
                    else if ($a == 3) {
                        $new_seed .= convNum(getRandomLastName($seeder));
                    }

                    // Domain
                    else if ($a == 4) {
                        $new_seed .= convNum(getRandomDomain($seeder));
                    }

                    // Email 2 random digits
                    else if ($a == 5) {
                        $new_seed .= convNum(mt_rand(10,99));
                    }
                    
                    // Picture
                    else if ($a == 6) {
                        $picture = getRandom("picture", $gender);
                        $new_seed .= convNum($picture[1]);
                    }
                }
            // Else, verify that the supplied seed is a valid seed and correct each section accordingly
            } else {
                $seed_ex = seedData($seed);

                // Seed for random numbers if there are fixes
                mt_srand($seed);

                // Gender
                if ($seed_ex[0] < 1 || $seed_ex[0] > 2) {
                    $seed_ex[0] = mt_rand(1, 2);
                }

                // First Name
                if (!validPerson($seed_ex[0], $seed_ex[1])) {
                    $seed_ex[1] = getRandomFirstName($seed_ex[0], $seed);
                }

                // Last Name
                if ($seed_ex[2] > numberOfEntries("last_name")) {
                    $seed_ex[2] = getRandomLastName($seed);
                }

                // Domain
                if ($seed_ex[3] > numberOfEntries("domain")) {
                    $seed_ex[3] = getRandomDomain($seed);
                }

                // Email (2 random digits)
                if ($seed_ex[4] < 10 && $seed_ex[4] > 99) {
                    $seed_ex[4] = mt_rand(10, 99);
                }
                
                // Picture [0, varies]
                // Male
                if ($seed_ex[0] == 1) {
                    if ($seed_ex[5] > male_pics()) {
                        $picture = getRandom("picture", $seed_ex[0]);
                        $seed_ex[5] = $picture[1];
                    }
                // Female
                } else {
                    if ($seed_ex[5] > female_pics()) {
                        $picture = getRandom("picture", $seed_ex[0]);
                        $seed_ex[5] = $picture[1];
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

            $first = getSpecific("first_name", $seedinfo[1], $seedinfo[0]);
            $last = getSpecific("last_name", $seedinfo[2]);
            $domain = getSpecific("domain", $seedinfo[3], $seedinfo[0], array($first, $last, $seedinfo[4]));
            $picture = getSpecific("picture", $seedinfo[5], $seedinfo[0]);
            $json = array(
                "user" => array(
                    "gender" => gender($seedinfo[0]),
                    "name" => array(
                        "first" => ucfirst($first),
                        "last" => ucfirst($last)
                    ),
                    "email" => $domain,
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
        return "male";
    } else {
        return "female";
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
