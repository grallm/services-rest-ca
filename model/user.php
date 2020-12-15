<?php
use Firebase\JWT\JWT;

/**
 * Find a user from a JWT key
 * @return user|null returns user if found, null if not
 */
function getUserFromJwt($jwtKey) {
  global $db;

  $query = "SELECT * FROM users WHERE jwt_api_key = ?";

  $statement = $db->prepare($query);
  $statement->execute([$jwtKey]);
  $result = $statement->fetch();

  return $statement->rowCount() == 1 ? $result : null;
}

/**
 * Register a user, generate a JWT API key
 * @return string JWT API key
 */
function registerUser($username) {
  global $db;
  global $secret;

  // Create user
  $query = "INSERT INTO users (username) VALUES (?)";
  
  $statement = $db->prepare($query);
  $statement->execute([$username]);
  
  // Encode API key with userId and created date
  $userId = $db->lastInsertId();
  $jwt = JWT::encode(array(
    'userId' => $userId,
    'username' => $username,
    // Issued at
    'iat' => time()
  ), $secret);

  // Add JWT user
  $query = "UPDATE users SET jwt_api_key = ? WHERE id = ?";
  
  $statement = $db->prepare($query);
  $statement->execute([
    $jwt,
    $userId
  ]);

  return $jwt;
}
