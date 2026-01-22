<?php
include "../db.php";

header('Content-Type: application/json; charset=utf-8');

$query = "SELECT id, park_name, location, geo_lat, geo_lon, district, adm_area 
          FROM parks 
          WHERE geo_lat IS NOT NULL AND geo_lon IS NOT NULL
          ORDER BY park_name";
$result = mysqli_query($mysql, $query);

$parks = array();

while ($row = mysqli_fetch_assoc($result)) {
    $parks[] = array(
        'id' => intval($row['id']),
        'name' => $row['park_name'] ?: 'Площадка без названия',
        'location' => $row['location'] ?: '',
        'lat' => floatval($row['geo_lat']),
        'lon' => floatval($row['geo_lon']),
        'district' => $row['district'] ?: '',
        'adm_area' => $row['adm_area'] ?: ''
    );
}

echo json_encode($parks, JSON_UNESCAPED_UNICODE);
?>
