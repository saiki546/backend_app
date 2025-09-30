<?php
function sendSuccessResponse($data = null, $message = 'Success') {
    $response = [
        'success' => true,
        'message' => $message,
        'data' => $data
    ];
    echo json_encode($response);
    exit;
}

function sendErrorResponse($message = 'Error', $code = 400) {
    http_response_code($code);
    $response = [
        'success' => false,
        'message' => $message,
        'error' => $message
    ];
    echo json_encode($response);
    exit;
}
?>

