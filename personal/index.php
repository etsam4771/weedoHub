<?php
/**
 * Web Terminal Interface
 * Can be used both as CLI and Web Interface
 */

// Check if this is an AJAX request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    ob_clean(); // Clear any output buffer
    
    $command = $_POST['command'] ?? '';
    
    if (empty($command)) {
        echo json_encode(['success' => false, 'output' => 'No command provided'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $result = executeCommand($command);
    echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// If not AJAX, check if CLI or serve web interface
if (php_sapi_name() === 'cli') {
    // CLI Mode
    $cli = new WebCLI();
    $cli->run($argv);
    exit;
}

// Function to execute commands
function executeCommand($command) {
    $command = trim($command);
    
    // Parse the command
    $parts = explode(' ', $command, 2);
    $mainCmd = $parts[0];
    $args = isset($parts[1]) ? $parts[1] : '';
    
    $output = '';
    $success = true;
    
    try {
        switch ($mainCmd) {
            case 'help':
                $output = getHelpText();
                break;
                
            case 'clear':
                $output = '';
                break;
                
            case 'get':
                $output = httpGet($args);
                break;
                
            case 'post':
                $output = httpPost($args);
                break;
                
            case 'curl':
                $output = executeCurl($command);
                break;
                
            case 'ls':
            case 'pwd':
            case 'whoami':
            case 'date':
            case 'uname':
            case 'df':
            case 'free':
            case 'uptime':
                $output = executeShellCommand($command);
                break;
                
            default:
                $output = executeShellCommand($command);
        }
    } catch (Exception $e) {
        $success = false;
        $output = "Error: " . $e->getMessage();
    }
    
    return [
        'success' => $success,
        'output' => $output,
        'command' => $command
    ];
}

function httpGet($args) {
    $url = trim($args);
    if (empty($url)) {
        return "Usage: get <url>";
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $result = "Error: " . curl_error($ch);
    } else {
        $result = "HTTP Status: $httpCode\n\n" . $response;
    }
    
    curl_close($ch);
    return $result;
}

function httpPost($args) {
    $parts = explode(' ', $args, 2);
    if (count($parts) < 2) {
        return "Usage: post <url> <data>";
    }
    
    $url = $parts[0];
    $data = $parts[1];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $result = "Error: " . curl_error($ch);
    } else {
        $result = "HTTP Status: $httpCode\n\n" . $response;
    }
    
    curl_close($ch);
    return $result;
}

function executeCurl($command) {
    $output = [];
    $returnVar = 0;
    exec($command . ' 2>&1', $output, $returnVar);
    return implode("\n", $output);
}

function executeShellCommand($command) {
    // Security: whitelist allowed commands
    $allowedCommands = ['ls', 'pwd', 'whoami', 'date', 'uname', 'df', 'free', 'uptime', 'curl', 'wget', 'ping', 'echo', 'cat', 'head', 'tail', 'wc'];
    $cmd = explode(' ', $command)[0];
    
    if (!in_array($cmd, $allowedCommands)) {
        return "Command '$cmd' is not allowed for security reasons.\nAllowed: " . implode(', ', $allowedCommands);
    }
    
    $output = [];
    $returnVar = 0;
    
    // Change to the web directory for context
    $currentDir = __DIR__;
    $fullCommand = "cd '$currentDir' && $command 2>&1";
    
    exec($fullCommand, $output, $returnVar);
    
    $result = implode("\n", $output);
    if ($returnVar !== 0) {
        $result .= "\n\nExit code: $returnVar";
    }
    
    return $result ?: "(No output)";
}

function getHelpText() {
    return <<<HELP
╔══════════════════════════════════════════════════════════╗
║              WEB TERMINAL - COMMAND REFERENCE             ║
╚══════════════════════════════════════════════════════════╝

📡 WEB COMMANDS:
  get <url>                 - Make HTTP GET request
  post <url> <data>         - Make HTTP POST request
  curl [options] <url>      - Execute curl with full options

💻 SYSTEM COMMANDS:
  ls [-la] [path]           - List directory contents
  pwd                       - Print working directory
  whoami                    - Show current user
  date                      - Show current date/time
  uname -a                  - Show system information
  df -h                     - Show disk usage
  free -h                   - Show memory usage
  uptime                    - Show system uptime
  cat <file>                - Display file contents
  echo <text>               - Print text to terminal

🛠️  TERMINAL COMMANDS:
  clear                     - Clear the terminal screen
  help                      - Show this help message

📝 EXAMPLES:
  → get https://api.github.com/users/github
  → post https://httpbin.org/post "name=test&value=123"
  → curl -I https://google.com
  → ls -la
  → cat index.php
  → echo "Hello Web Terminal!"
  → whoami
  → pwd

💡 TIP: Use ↑/↓ arrow keys to navigate command history
HELP;
}

class WebCLI {
    private $baseUrl = '';
    
    public function __construct() {
        // Check if running from command line
        if (php_sapi_name() !== 'cli') {
            die("This script must be run from command line\n");
        }
    }
    
    public function run($argv) {
        if (count($argv) < 2) {
            $this->showHelp();
            return;
        }
        
        $command = $argv[1];
        
        switch ($command) {
            case 'get':
                $this->httpGet($argv);
                break;
            case 'post':
                $this->httpPost($argv);
                break;
            case 'exec':
                $this->executeCommand($argv);
                break;
            case 'help':
                $this->showHelp();
                break;
            default:
                echo "Unknown command: $command\n";
                $this->showHelp();
        }
    }
    
    private function httpGet($argv) {
        if (count($argv) < 3) {
            echo "Usage: php index.php get <url> [headers]\n";
            return;
        }
        
        $url = $argv[2];
        $headers = isset($argv[3]) ? json_decode($argv[3], true) : [];
        
        echo "Making GET request to: $url\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if (!empty($headers)) {
            $headerArray = [];
            foreach ($headers as $key => $value) {
                $headerArray[] = "$key: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            echo "Error: " . curl_error($ch) . "\n";
        } else {
            echo "HTTP Status: $httpCode\n";
            echo "Response:\n";
            echo $response . "\n";
        }
        
        curl_close($ch);
    }
    
    private function httpPost($argv) {
        if (count($argv) < 3) {
            echo "Usage: php index.php post <url> [data] [headers]\n";
            return;
        }
        
        $url = $argv[2];
        $data = isset($argv[3]) ? $argv[3] : '';
        $headers = isset($argv[4]) ? json_decode($argv[4], true) : [];
        
        echo "Making POST request to: $url\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if (!empty($headers)) {
            $headerArray = [];
            foreach ($headers as $key => $value) {
                $headerArray[] = "$key: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            echo "Error: " . curl_error($ch) . "\n";
        } else {
            echo "HTTP Status: $httpCode\n";
            echo "Response:\n";
            echo $response . "\n";
        }
        
        curl_close($ch);
    }
    
    private function executeCommand($argv) {
        if (count($argv) < 3) {
            echo "Usage: php index.php exec <command>\n";
            return;
        }
        
        // Remove script name and 'exec' command
        array_shift($argv);
        array_shift($argv);
        
        $command = implode(' ', $argv);
        
        echo "Executing: $command\n";
        echo str_repeat('-', 50) . "\n";
        
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        
        foreach ($output as $line) {
            echo $line . "\n";
        }
        
        echo str_repeat('-', 50) . "\n";
        echo "Exit code: $returnVar\n";
    }
    
    private function showHelp() {
        echo <<<HELP

Web CLI Tool - Command Line Interface for Web Requests and Execution

Usage:
  php index.php [command] [arguments]

Commands:
  get <url> [headers]           Make a GET request to a URL
                                Headers should be JSON format (optional)
                                Example: php index.php get https://api.example.com

  post <url> [data] [headers]   Make a POST request to a URL
                                Data can be JSON or form data
                                Example: php index.php post https://api.example.com '{"key":"value"}'

  exec <command>                Execute a shell command
                                Example: php index.php exec ls -la

  help                          Show this help message

Examples:
  php index.php get https://jsonplaceholder.typicode.com/posts/1
  php index.php post https://httpbin.org/post 'test=data'
  php index.php exec curl -I https://google.com
  php index.php exec whoami

HELP;
    }
}

// Run the CLI (only in CLI mode)
if (php_sapi_name() === 'cli') {
    $cli = new WebCLI();
    $cli->run($argv);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Terminal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .terminal-container {
            width: 100%;
            max-width: 1200px;
            height: 80vh;
            background: rgba(0, 0, 0, 0.9);
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .terminal-header {
            background: linear-gradient(to right, #434343 0%, #2b2b2b 100%);
            padding: 10px 15px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #555;
        }

        .terminal-buttons {
            display: flex;
            gap: 8px;
        }

        .terminal-button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            cursor: pointer;
        }

        .btn-close { background: #ff5f56; }
        .btn-minimize { background: #ffbd2e; }
        .btn-maximize { background: #27c93f; }

        .terminal-title {
            flex: 1;
            text-align: center;
            color: #aaa;
            font-size: 12px;
            font-weight: bold;
        }

        .terminal-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #000;
            color: #00ff00;
            font-size: 14px;
            line-height: 1.6;
        }

        .terminal-body::-webkit-scrollbar {
            width: 8px;
        }

        .terminal-body::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        .terminal-body::-webkit-scrollbar-thumb {
            background: #555;
            border-radius: 4px;
        }

        .terminal-line {
            margin-bottom: 5px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .prompt {
            color: #00ff00;
            font-weight: bold;
        }

        .user {
            color: #5fd7ff;
        }

        .path {
            color: #f3a712;
        }

        .command {
            color: #fff;
        }

        .output {
            color: #00ff00;
            white-space: pre-wrap;
            margin-left: 0;
            font-family: 'Courier New', monospace;
        }

        .error {
            color: #ff5555;
        }

        .success {
            color: #50fa7b;
        }

        .terminal-input-container {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: #1a1a1a;
            border-top: 1px solid #333;
        }

        .input-prompt {
            color: #00ff00;
            margin-right: 10px;
            font-weight: bold;
        }

        .input-user {
            color: #5fd7ff;
        }

        .input-path {
            color: #f3a712;
        }

        #commandInput {
            flex: 1;
            background: transparent;
            border: none;
            color: #fff;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            outline: none;
            caret-color: #00ff00;
        }

        .welcome-text {
            color: #5fd7ff;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .loading {
            color: #f3a712;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }

        .cursor {
            display: inline-block;
            width: 8px;
            height: 16px;
            background: #00ff00;
            animation: cursorBlink 1s infinite;
            margin-left: 2px;
        }

        @keyframes cursorBlink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        .info {
            color: #8be9fd;
        }

        .warning {
            color: #ffb86c;
        }

        @media (max-width: 768px) {
            .terminal-container {
                height: 90vh;
            }
            
            .terminal-body {
                font-size: 12px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="terminal-container">
        <div class="terminal-header">
            <div class="terminal-buttons">
                <div class="terminal-button btn-close"></div>
                <div class="terminal-button btn-minimize"></div>
                <div class="terminal-button btn-maximize"></div>
            </div>
            <div class="terminal-title">Web Terminal v1.0</div>
        </div>
        
        <div class="terminal-body" id="terminal">
            <div class="welcome-text">
╔══════════════════════════════════════════════════════════╗
║          Welcome to Web Terminal Interface              ║
║          Type 'help' for available commands              ║
╚══════════════════════════════════════════════════════════╝
            </div>
        </div>
        
        <div class="terminal-input-container">
            <span class="input-prompt">
                <span class="input-user">guest</span>@<span class="input-path">webterminal</span>:~$
            </span>
            <input type="text" id="commandInput" autofocus autocomplete="off" spellcheck="false">
        </div>
    </div>

    <script>
        const terminal = document.getElementById('terminal');
        const commandInput = document.getElementById('commandInput');
        let commandHistory = [];
        let historyIndex = -1;

        // Focus input when clicking anywhere in terminal
        terminal.addEventListener('click', () => commandInput.focus());

        // Handle command input
        commandInput.addEventListener('keydown', async (e) => {
            if (e.key === 'Enter') {
                const command = commandInput.value.trim();
                
                if (command) {
                    // Add to history
                    commandHistory.unshift(command);
                    historyIndex = -1;
                    
                    // Display command
                    addLine(`<span class="prompt"><span class="user">guest</span>@<span class="path">webterminal</span>:~$</span> <span class="command">${escapeHtml(command)}</span>`);
                    
                    // Clear input
                    commandInput.value = '';
                    
                    // Handle clear command locally
                    if (command.toLowerCase() === 'clear') {
                        terminal.innerHTML = '';
                        return;
                    }
                    
                    // Execute command
                    await executeCommand(command);
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (historyIndex < commandHistory.length - 1) {
                    historyIndex++;
                    commandInput.value = commandHistory[historyIndex];
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (historyIndex > 0) {
                    historyIndex--;
                    commandInput.value = commandHistory[historyIndex];
                } else if (historyIndex === 0) {
                    historyIndex = -1;
                    commandInput.value = '';
                }
            } else if (e.key === 'Tab') {
                e.preventDefault();
                // Auto-complete could be added here
            }
        });

        async function executeCommand(command) {
            // Show loading
            const loadingId = 'loading-' + Date.now();
            addLine(`<span class="loading" id="${loadingId}">Executing...</span>`);
            
            try {
                const formData = new FormData();
                formData.append('command', command);
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                // Remove loading
                document.getElementById(loadingId)?.remove();
                
                // Display output
                if (result.output) {
                    const outputClass = result.success ? 'output' : 'error';
                    addLine(`<span class="${outputClass}">${escapeHtml(result.output)}</span>`);
                }
            } catch (error) {
                document.getElementById(loadingId)?.remove();
                addLine(`<span class="error">Error: ${error.message}</span>`);
            }
            
            // Scroll to bottom
            terminal.scrollTop = terminal.scrollHeight;
        }

        function addLine(content) {
            const line = document.createElement('div');
            line.className = 'terminal-line';
            line.innerHTML = content;
            terminal.appendChild(line);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Auto-focus input on load
        window.addEventListener('load', () => {
            commandInput.focus();
        });

        // Keep focus on input
        document.addEventListener('click', (e) => {
            if (!e.target.closest('a')) {
                commandInput.focus();
            }
        });
    </script>
</body>
</html>
