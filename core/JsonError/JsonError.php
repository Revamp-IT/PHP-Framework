<?php

namespace Revamp\Core\JsonError;

define("DM1", ["The unique key was repeated", 400, "DM1"]);

define("R1", ["The URI is not bound to any controller method", 400, "R1"]);
define("R2", ["Some fields of the specified Request Template were not filled in the request", 400, "R2"]);
define("R3", ["Some fields of the specified Response Template were not filled in the response", 400, "R3"]);

define("A1", ["Access-Token or Refresh-Token Headers were not provided", 401, "A1"]);
define("A2", ["The Refresh-Token has expired or is invalid", 401, "A2"]);

define("U1", ["There is no User with provided Login", 400, "U1"]);
define("U2", ["Provided Password is incorrect", 400, "U2"]);
define("U3", ["User with provided Login has already been registered", 400, "U3"]);

class JsonError implements JsonErrorInterface
{
    public function throw(array $error): void
    {
        http_response_code($error[1]);

        echo json_encode([
            'shortcode' => $error[2],
            'message' => $error[0],
        ]);

        die();
    }
}