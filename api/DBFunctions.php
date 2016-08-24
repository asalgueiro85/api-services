<?php
class DBFunctions{

    public function executeProcedure($params)
    {
        $data = array();
//        $db = R::setup('pgsql:host=192.168.10.1;dbname=cbook_crm_db','cbook_crm_usr','ZyAua6MZN9Sx8Kyw');
        $db = $this->getConnection();
        $stmt = $db->prepare('select public.func_search_json(:mapping_indoor, :mapping_beActive_interestingPlaces, :zone, :city, :region)');
        $stmt->bindValue('mapping_beActive_interestingPlaces', $params->mapping_beActive_interestingPlaces);
        $stmt->bindValue('mapping_indoor', $params->mapping_indoor);
        $stmt->bindValue('zone', $params->zone);
        $stmt->bindValue('city', $params->city);
        $stmt->bindValue('region', $params->region);
        $stmt->execute();

        while (($row = $stmt->fetchColumn()) !== false) {
            /*$row=substr($row,1);
            $row = substr ($row, 0, strlen($row) - 1);

            $array = explode(",", $row);
            $data[] = array(
                'id_zone'      => $array[0],
                'lat_zone'      => floatval($array[1]),
                'lng_zone'      => floatval($array[2]),
                'id_city'      => $array[3],
                'lat_city'      => floatval($array[4]),
                'lng_city'      => floatval($array[5]),
                'id_country'      => $array[6],
                'lat_country'      => floatval($array[7]),
                'lng_country'      => floatval($array[8]),
                'url' =>  $array[9]
            );*/
            $data[] = $row;
        }
        $db = null;
        return $data;
    }

    private function getConnection() {
        $dbhost="192.168.10.1";
        $dbuser="cbook_crm_usr";
        $dbpass="ZyAua6MZN9Sx8Kyw";
        $dbname="cbook_crm_db";
        $dbh = new PDO("pgsql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }

}
