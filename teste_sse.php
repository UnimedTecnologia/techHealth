<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

for ($i = 0; $i <= 100; $i += 10) {
    echo "data: " . json_encode(['percent' => $i, 'message' => "Progresso: $i%"]) . "\n\n";
    ob_flush();
    flush();
    sleep(1);
}
