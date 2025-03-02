<?php
// 用來存放 OpenAI API Key，請根據您的需求替換
$openai_api_key = 'OpenAI API Key';

// 使用 OpenAI API - php

// 從前端接收 JSON 格式的輸入數據
$input_data = json_decode(file_get_contents('php://input'), true);
$user_message = $input_data['message']; // 獲取使用者輸入的消息
$chat_history = $input_data['history']; // 獲取聊天歷史記錄

// 合併之前的對話歷史（如果有）
$messages = [];

// 加入系統提示
$messages[] = ['role' => 'system', 'content' => '
        Context：我是一名使用 ChatGPT 的使用者，經常需要設計清晰而有效的 Prompt，用於研究、教學和技術問題解決。然而，有時候我不確定如何組織 Prompt，讓 ChatGPT 給出最佳的回應。
        我希望 ChatGPT 在回應時使用臺灣特有的慣用語和口語表達，讓交流更自然。
        Objective：我希望 ChatGPT 能扮演一位「Prompt 大師」，幫助我改進或重寫我的 Prompt，最後並用( # CONTEXT #, # OBJECTIVE #, # STYLE #, # TONE #, # AUDIENCE #, # RESPONSE #)的格式，使其更符合預期的輸出目標。 
        Style：提供的建議應該專業且系統化，並包含實例。適當時，對錯誤的 Prompt 給出簡要分析，指出需要改進的地方。
        Tone：語氣友好、鼓勵且具教育意義，讓我能輕鬆學習並應用建議。
        Audience：請依照主題設定適當的人設。
        Response：請給出具體步驟，並提供一個實例來示範如何設計優質 Prompt。
       '];

foreach ($chat_history as $message) {
    // 將聊天歷史中的每條消息轉換為 OpenAI API 所需的格式
    $messages[] = ['role' => $message['sender'] == 'user' ? 'user' : 'assistant', 'content' => $message['text']];
}
// 將使用者最新的消息添加到消息數組中
$messages[] = ['role' => 'user', 'content' => $user_message];





// 發送請求到 OpenAI API
$ch = curl_init(); // 初始化 cURL
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions'); // 設定 API 請求的 URL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 設定返回結果為字串
curl_setopt($ch, CURLOPT_POST, true); // 設定請求方法為 POST
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json', // 設定請求頭為 JSON 格式
    'Authorization: Bearer ' . $openai_api_key, // 設定 API Key 驗證
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    //'model' => 'gpt-4o-mini',  // 選擇使用的模型（請根據需求替換）
    'model' => 'gpt-4o',  // 選擇使用的模型（請根據需求替換）
    'messages' => $messages, // 傳送消息數組
    'max_tokens' => 10000 // 設定最大生成的 token 數
]));

$response = curl_exec($ch); // 執行 cURL 請求並獲取回應
curl_close($ch); // 關閉 cURL 會話

// 解析並返回回應
$response_data = json_decode($response, true); // 將回應解析為 JSON 格式
$bot_reply = $response_data['choices'][0]['message']['content'] ?? '抱歉，發生了錯誤。'; // 獲取機器人回覆，若出錯則返回預設訊息

// 返回回應
echo json_encode(['reply' => $bot_reply]); // 將機器人回覆以 JSON 格式返回給前端
?>
