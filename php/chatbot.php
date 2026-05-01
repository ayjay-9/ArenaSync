<?php
require_once '../db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (empty($gemini_api_key)) {
    http_response_code(500);
    echo json_encode(['error' => 'Gemini API key is not configured']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if ($message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

$system_prompt = "You are the ArenaSync assistant. ArenaSync is a gaming event platform where attendees discover and book esports and community gaming events, organisers create and manage events, and admins oversee the system. Keep answers short and friendly. If asked something unrelated to gaming events or the site, politely steer the conversation back.";

$payload = [
    'contents' => [
        ['parts' => [['text' => $message]]]
    ],
    'systemInstruction' => [
        'parts' => [['text' => $system_prompt]]
    ]
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . urlencode($gemini_api_key);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'Chatbot service error']);
    exit;
}

$data = json_decode($response, true);
$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't think of a reply.";

echo json_encode(['reply' => $reply]);
?>
