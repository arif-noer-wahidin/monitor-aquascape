<?php

/**
 * =================================================================
 * Aquascape Dashboard - Backend API Proxy
 * =================================================================
 *
 * Bertindak sebagai proxy untuk berkomunikasi dengan Google Apps Script.
 * Menerima request dari frontend, meneruskannya ke Google Script,
 * dan mengembalikan respons dalam format JSON.
 *
 * PHP version 8.3
 *
 * @category   API
 * @package    AquascapeDashboard
 * @author     Gemini (Diperbaiki)
 * @version    1.1.0
 */

// Atur header untuk respons JSON
header('Content-Type: application/json');

// Muat file konfigurasi
require_once 'config.php';

/**
 * Fungsi untuk membuat permintaan ke Google Apps Script menggunakan cURL.
 * Fungsi ini sekarang dapat menangani GET dan POST.
 *
 * @param array $params Parameter yang akan dikirim.
 * @param string $method Metode HTTP ('GET' atau 'POST').
 * @return array Hasil respons dari Google Script API (sudah di-decode dari JSON).
 */
function callGoogleScriptAPI(array $params, string $method = 'GET'): array
{
    $url = GOOGLE_SCRIPT_URL;
    $ch = curl_init();

    if ($method === 'POST') {
        // Untuk metode POST, kirim data sebagai JSON di body
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        // Atur header untuk menandakan bahwa kita mengirim JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    } else {
        // Untuk metode GET, tambahkan parameter ke URL
        $url .= '?' . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
    }

    // Opsi cURL umum
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Penting untuk Google Apps Script
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // Eksekusi cURL
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Cek error cURL
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        error_log("cURL Error: " . $error_msg);
        return ['error' => 'cURL Error: ' . $error_msg];
    }
    
    curl_close($ch);

    // Cek status HTTP
    if ($httpCode !== 200) {
        $errorDetails = json_decode($response, true);
        $errorMessage = $errorDetails['message'] ?? $response;
        error_log("API request failed with status {$httpCode}. Response: " . $response);
        return ['error' => "API request failed with status code {$httpCode}. Details: {$errorMessage}"];
    }

    // Decode respons JSON
    $decoded_response = json_decode($response, true);

    // Cek jika JSON tidak valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Jika respons adalah string 'OK' atau pesan sukses sederhana, bungkus dalam JSON yang valid
        if (trim($response) !== '') {
             return ['message' => trim($response)];
        }
        error_log("Invalid JSON response from API: " . $response);
        return ['error' => 'Invalid JSON response from API. Raw: ' . $response];
    }

    return $decoded_response;
}

// Dapatkan 'action' dari request GET atau POST
$action = $_REQUEST['action'] ?? '';

$response_data = [];

try {
    switch ($action) {
        // Ambil data real-time dan histori (tetap menggunakan GET)
        case 'getAllData':
            $timeframe = $_GET['timeframe'] ?? 'all';
            $historyAction = 'history';
            if ($timeframe === '1hour') $historyAction = 'history1hour';
            elseif ($timeframe === '1day') $historyAction = 'history1day';
            elseif ($timeframe === '1week') $historyAction = 'history1week';
            
            $response_data['realtime'] = callGoogleScriptAPI(['action' => 'realtime'], 'GET');
            $response_data['history'] = callGoogleScriptAPI(['action' => $historyAction, 'limit' => $_GET['limit'] ?? 8640], 'GET');
            break;
            
         // NEW: Mendapatkan definisi rentang
        case 'getRangeDefinitions':
            // Panggil Google Script API dengan parameter 'getRangeDefinitions'
            $response_data = callGoogleScriptAPI(['action' => 'getRangeDefinitions'], 'GET');
            break;
            
        // NEW: Mendapatkan aturan fuzzy
        case 'getFuzzyRules':
            // Panggil Google Script API dengan parameter 'getFuzzyRules'
            $response_data = callGoogleScriptAPI(['action' => 'getFuzzyRules'], 'GET');
            break;

        // Kirim perintah ke relay atau timer (menggunakan POST)
        case 'setStatus':
             if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405); // Method Not Allowed
                throw new Exception("Metode setStatus harus menggunakan POST.");
            }
            // Ambil semua parameter dari body POST
            $params = $_POST;
            $params['action'] = 'setStatus'; 

            if (empty($params)) {
                throw new Exception("Tidak ada parameter yang valid untuk setStatus.");
            }
            // Panggil API menggunakan metode POST
            $response_data = callGoogleScriptAPI($params, 'POST');
            break;

        default:
            http_response_code(400); // Bad Request
            $response_data = ['error' => 'Aksi tidak valid.'];
            break;
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    $response_data = ['error' => $e->getMessage()];
}

// Kirim hasil akhir sebagai JSON
echo json_encode($response_data);

?>
