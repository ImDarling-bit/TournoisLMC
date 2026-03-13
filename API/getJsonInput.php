<?php
function getJsonInput() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if ($raw != '' && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        switch (json_last_error()) {
        case 0:
            $e = "No errors";
            break;
        case 1:
            $e = "Maximum stack depth exceeded";
            break;;
        case 2:
        $e = "Invalid or malformed JSON";
            break;
        case 3:
            $e = "Control character error";
            break;
        case 4:
            $e = "Syntax error";
            break;
        case 5:
        $e = "Malformed UTF-8 characters";
            break;
        default:
            $e =  "Unknown error";
            break;
        }
        echo json_encode(['error' => $e, 'message' => $raw]);
        exit;
    }
    return $data ?: [];
}
?>