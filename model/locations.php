<?php
/**
 * Get all countries
 * @return array All countries with id and country (name)
 */
function getAllCountries() {
  global $db;

  $query = "SELECT id, country FROM countries";

  $statement = $db->prepare($query);
  $statement->execute();
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  $statement->closeCursor();

  return $result;
}

/**
 * Get all counties of a specific country
 * @param int country Country id
 * @return array All counties of this country
 */
function getCountryCounties($country) {
  global $db;

  $query = "SELECT id, name FROM counties WHERE country_id = ?";

  $statement = $db->prepare($query);
  $statement->execute([$country]);
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  $statement->closeCursor();

  return $result;
}

/**
 * Get all towns of a specific county
 * @param int county County id
 * @return array All towns of this county
 */
function getCountryCountyTowns($county) {
  global $db;

  $query = "SELECT townId, townName FROM towns WHERE countyID = ?";

  $statement = $db->prepare($query);
  $statement->execute([$county]);
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  $statement->closeCursor();

  return $result;
}
