<?php
require 'DBFunctions.php';
class Functions{

    public function searchResult($indoor, $beActive, $interestingPlaces, $location)
    {
        $locations = array();
        foreach ($location as $loc) {
            $temp = explode(':', $loc);
            $locations[$temp[0]] = $temp[1];
        }
        $locationType = 0;
        $locationId = "";

        $region = 0;
        $city = 0;
        $zone = "{}";

        switch ($locationType) {
            case 1:
                $region = $locationId;
                break;
            case 3:
                $city = $locationId;
                break;
            default:
                $zone = "{";
                $zones = explode(',', $locationId);
                if (!empty($zones)) {
                    for ($i = 0; $i < count($zones); $i++) {
                        $zone = $zone . $zones[$i];
                        if ($i < count($zones) - 1) {
                            $zone = $zone . ",";
                        }
                    }
                }
                $zone = $zone . "}";
        }

        //store procedure
        $params = new \stdClass();
        $params->mapping_beActive_interestingPlaces = $this->postgresArray(array_merge((array)$beActive, (array)$interestingPlaces));
        $params->mapping_indoor = $this->postgresArray((array)$indoor);
        $params->zone = $zone;
        $params->city = $city;
        $params->region = $region;
$dbfunc = new DBFunctions();
        $data = $dbfunc->executeProcedure($params);

        return $data;
    }

    private function postgresArray($collection = array())
    {
        $result = "{";

        for ($i = 0; $i < count($collection); $i++) {
            if (strlen($result) == 1)
                $result = $result . $collection[$i];
            else
                $result = $result . ',' . $collection[$i];
        }

        $result = $result . "}";

        return $result;
    }
}
