<?php
require_once './JWT.php';

$secret = 'eG?c7dQr"54L//;T';

$dsn = "mysql:host=localhost;dbname=locations";
$username = "root";
$password = "root";

// Create connection
try {
  $db = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

// Import models
require_once './model/user.php';
require_once './model/locations.php';

/* REST ACCESS */
/**
 * Centralized general return content
 * @param string type
 * @return array reponse for body and code for status code
 */
function generalResponse ($type) {
  $response = '';
  $code = 0;

  switch ($type) {
    case 'general-error':
      $response = [
        'error' => [
          'message' => 'An error occurred',
          'code' => '0'
        ]
      ];
      $code = 500;
      break;

    case 'not-found':
    default:
      $response = [
        'error' => [
          'message' => 'Action not found',
          'code' => '1'
        ]
      ];
      $code = 404;
  }

  return [
    'response' => $response,
    'statusCode' => $code
  ];
}

$action = filter_input(INPUT_GET, 'action') == NULL ? "default" : filter_input(INPUT_GET, 'action');

$statusCode = 404;
$responseBody = generalResponse('not-found')['response'];

/**
 * Check if the request has a key and that it's valid
 * @return boolean true if valid, false else
 */
function checkKey() {
  global $responseBody;
  global $statusCode;

  // Check if parameter is set
  if (filter_input(INPUT_GET, 'key') == NULL) {
    // Missing key
    $responseBody = [
      'error' => [
        'message' => 'Missing API Key',
        'code' => '0'
      ]
    ];
    $statusCode = 401;
    return false;
  }

  // Checking Key validity
  $user = getUserFromJwt($_GET['key']);
  if (is_null($user)) {
    // Not valid key
    $responseBody = [
      'error' => [
        'message' => 'Invalid API Key',
        'code' => '0'
      ]
    ];
    $statusCode = 400;
    return false;
  }
  return true;
}

switch ($action) {
  case "register":
    // Register the user if fields exist
    if (filter_input(INPUT_GET, 'username') == NULL || empty(filter_input(INPUT_GET, 'username'))) {
      $responseBody = [
        'error' => [
          'message' => 'A username is required to register'
        ]
      ];
      $statusCode = 400;
    }

    // Register the user with it's suername
    $key = registerUser($_GET['username']);

    // Return key generated or an error if no key
    $responseBody = ($key == NULL) ? generalResponse('general-error')['response'] : [
      'key' => $key
    ];
    $statusCode = ($key == NULL) ? generalResponse('general-error')['code'] : 200;
    break;

  case "countries":
    if (checkKey()) {
      // Get all countries
      $responseBody = getAllCountries();
      $statusCode = 200;
    }
    break;

  case "counties":
    if (checkKey()) {
      // Get all counties of a country
      $responseBody = getCountryCounties($_GET['country']);
      $statusCode = 200;
    }
    break;

  case "towns":
    if (checkKey()) {
      // Get all towns of a county
      $responseBody = getCountryCountyTowns($_GET['county']);
      $statusCode = 200;
    }
    break;

  default:
}

http_response_code($statusCode);
header('Content-Type: application/json');
echo json_encode($responseBody);