<?php
// AMANDA AI - Cognitive Processing Core (V1)
// Default timezone
date_default_timezone_set('America/Fortaleza');

// Receive command from Python
$input_command = isset($argv[1]) ? mb_strtolower(trim($argv[1]), 'UTF-8') : '';

if (empty($input_command)) {
    die("No command received.");
}

// Accent removal function
function removeAccents($string) {
    return preg_replace(
        array("/(á|à|ã|â|ä)/", "/(é|è|ê|ë)/", "/(í|ì|î|ï)/", "/(ó|ò|õ|ô|ö)/", "/(ú|ù|û|ü)/", "/(ç)/", "/(ñ)/"),
        explode(" ", "a e i o u c n"),
        $string
    );
}

// Vosk acoustic filter
function normalizePhonetics($text) {
    $phonetic_dictionary = [
        'zero' => '0', 'um' => '1', 'dois' => '2', 'três' => '3', 'tres' => '3',
        'quatro' => '4', 'cinco' => '5', 'seis' => '6', 'sete' => '7',
        'oito' => '8', 'nove' => '9', 'dez' => '10', 'onze' => '11',
        'doze' => '12', 'treze' => '13', 'catorze' => '14', 'quatorze' => '14',
        'quinze' => '15', 'dezesseis' => '16', 'dezessete' => '17',
        'dezoito' => '18', 'dezenove' => '19', 'vinte' => '20',
        'trinta' => '30', 'quarenta' => '40', 'cinquenta' => '50',
        'sessenta' => '60', 'setenta' => '70', 'oitenta' => '80', 'noventa' => '90',
        'cem' => '100', 'cento' => '100', 'duzentos' => '200', 'trezentos' => '300',
        'mil' => '1000',
        'mas' => '+', 'sim' => '5', 'sérios' => '7', 'vento' => '20',
        'mais' => '+', 'menos' => '-', 'vezes' => '*', 'multiplicado por' => '*',
        'dividido por' => '/', 'dividido' => '/'
    ];

    $words = explode(' ', $text);
    foreach ($words as &$word) {
        if (array_key_exists($word, $phonetic_dictionary)) {
            $word = $phonetic_dictionary[$word];
        }
    }
    return implode(' ', $words);
}

$processed_input = removeAccents(normalizePhonetics($input_command));

// ==========================================
// OS AUTOMATION MODULE (OPEN PROGRAMS)
// ==========================================
if (strpos($processed_input, 'abrir') !== false || strpos($processed_input, 'iniciar') !== false) {
    
    // Inject GUI environment variables for Ubuntu (X11/Wayland context)
    putenv("DISPLAY=:0");
    putenv("XDG_RUNTIME_DIR=/run/user/1000");
    putenv("DBUS_SESSION_BUS_ADDRESS=unix:path=/run/user/1000/bus");
    
    if (strpos($processed_input, 'chrome') !== false || strpos($processed_input, 'navegador') !== false) {
        exec("nohup google-chrome > /dev/null 2>&1 &");
        echo "Abrindo o Google Chrome.";
        exit;
    }
    
    if (strpos($processed_input, 'calculadora') !== false) {
        exec("nohup gnome-calculator > /dev/null 2>&1 &");
        echo "Calculadora iniciada.";
        exit;
    }
    
    if (strpos($processed_input, 'terminal') !== false) {
        exec("nohup gnome-terminal > /dev/null 2>&1 &");
        echo "Terminal aberto.";
        exit;
    }

    if (strpos($processed_input, 'pastas') !== false || strpos($processed_input, 'arquivos') !== false) {
        exec("nohup nautilus > /dev/null 2>&1 &");
        echo "Gerenciador de arquivos aberto.";
        exit;
    }

    echo "Entendi o comando, mas este programa ainda não está mapeado no meu núcleo.";
    exit;
}

// ==========================================
// NATIVE MODULES (Time, Date, Math)
// ==========================================
if (strpos($processed_input, 'horas') !== false || strpos($processed_input, 'que horas') !== false || strpos($processed_input, 'horario') !== false) {
    echo "Agora são " . date('H') . " horas e " . date('i') . " minutos.";
    exit;
}

if (strpos($processed_input, 'dia e hoje') !== false || strpos($processed_input, 'que dia') !== false || strpos($processed_input, 'data de hoje') !== false) {
    $months = [1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril', 5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto', 9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'];
    echo "Hoje é dia " . date('d') . " de " . $months[(int)date('m')] . " de " . date('Y') . ".";
    exit;
}

if (preg_match('/(\d+)\s*([\+\-\*\/])\s*(\d+)/', $processed_input, $matches)) {
    $num1 = (float)$matches[1];
    $operator = $matches[2];
    $num2 = (float)$matches[3];
    $result = 0;

    switch ($operator) {
        case '+': $result = $num1 + $num2; break;
        case '-': $result = $num1 - $num2; break;
        case '*': $result = $num1 * $num2; break;
        case '/': 
            if ($num2 == 0) {
                echo "Matematicamente impossível dividir por zero.";
                exit;
            }
            $result = $num1 / $num2; 
            break;
    }
    echo "O resultado é " . $result . ".";
    exit;
}

// ==========================================
// DATABASE KNOWLEDGE RETRIEVAL MODULE
// ==========================================
$db_host = '127.0.0.1';
$db_name = 'ia_amanda'; 
$db_user = 'root'; 
$db_pass = 'code'; 

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT keyword, response FROM knowledge_base ORDER BY LENGTH(keyword) DESC");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($records as $row) {
        $clean_key = removeAccents(mb_strtolower(trim($row['keyword']), 'UTF-8'));
        
        if (strpos($processed_input, $clean_key) !== false) {
            echo $row['response'];
            exit;
        }
    }
} catch (PDOException $e) {
    echo "Erro BD: " . $e->getMessage();
    exit;
}

// Fallback response
echo "Ainda não tenho essa informação registrada na minha base de conhecimento.";
exit;
?>