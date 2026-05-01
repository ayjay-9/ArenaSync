<?php
require_once '../db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (empty($openrouter_api_key)) {
    http_response_code(500);
    echo json_encode(['error' => 'API key is not configured']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if ($message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

$events_context = '';
$result = $conn->query("
    SELECT e.id, e.date_time, g.name AS game_name,
           COALESCE(NULLIF(TRIM(CONCAT(u.first_name, ' ', u.last_name)), ''), u.company, u.email) AS organiser_name
    FROM events e
    JOIN games g ON e.game_id = g.id
    JOIN users u ON e.organiser_id = u.id
    WHERE e.date_time >= NOW()
    ORDER BY e.date_time
    LIMIT 10
");
if ($result && $result->num_rows > 0) {
    $lines = [];
    while ($row = $result->fetch_assoc()) {
        $dt = date('D j M Y, g:ia', strtotime($row['date_time']));
        $lines[] = "- {$row['game_name']} on {$dt} (organised by {$row['organiser_name']}, event ID #{$row['id']})";
    }
    $events_context = "\n\nUpcoming events on ArenaSync:\n" . implode("\n", $lines);
} else {
    $events_context = "\n\nThere are currently no upcoming events on ArenaSync.";
}

$organisers_context = '';
$org_result = $conn->query("
    SELECT COALESCE(NULLIF(TRIM(CONCAT(first_name, ' ', last_name)), ''), company, email) AS organiser_name,
           company
    FROM users
    WHERE role = 'organiser'
    ORDER BY first_name, company
");
if ($org_result && $org_result->num_rows > 0) {
    $org_lines = [];
    while ($row = $org_result->fetch_assoc()) {
        $label = $row['organiser_name'];
        if ($row['company'] && $row['company'] !== $row['organiser_name']) {
            $label .= " ({$row['company']})";
        }
        $org_lines[] = "- $label";
    }
    $organisers_context = "\n\nOrganisers registered on ArenaSync:\n" . implode("\n", $org_lines);
} else {
    $organisers_context = "\n\nThere are currently no organisers registered on ArenaSync.";
}

$system_prompt = "You are the ArenaSync assistant. ArenaSync is a gaming event platform where attendees discover and book esports and community gaming events, organisers create and manage events, and admins oversee the system. Keep answers short and friendly. If asked something unrelated to gaming events or the site, politely steer the conversation back. Only share event or organiser details when the user specifically asks — do not mention them unprompted. Only refer to the data below and never invent any." . $events_context . $organisers_context;

$payload = [
    'model' => 'google/gemma-3-12b-it:free',
    'messages' => [
        ['role' => 'user', 'content' => $system_prompt . "\n\nUser: " . $message]
    ]
];

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $openrouter_api_key
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status !== 200) {
    $error_msg = $status === 429
        ? 'The assistant is busy right now — please try again in a moment.'
        : 'Chatbot service error';
    http_response_code($status === 429 ? 429 : 502);
    echo json_encode(['error' => $error_msg]);
    exit;
}

$data = json_decode($response, true);
$reply = $data['choices'][0]['message']['content'] ?? "Sorry, I couldn't think of a reply.";

echo json_encode(['reply' => $reply]);
?>
