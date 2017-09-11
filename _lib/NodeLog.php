<?php
use Lib\Config;

class NodeLog
{
    const NODE_DEF_HOST = 'errorlog.studigt.com.br';
    const NODE_DEF_PORT = 8888;

    public static $logs = array();

    private $_host;
    private $_port;
    /**
     * @param String $host
     * @param Integer $port
     * @return NodeLog
     */
    static function connect($host = null, $port = null)
    {
        return new self(is_null($host) ? self::$_defHost : $host, is_null($port) ? self::$_defPort : $port);
    }
    function __construct($host = null, $port = null)
    {
        $this->_host = $host;
        $this->_port = $port;
    }
    /**
     * @param String $log
     * @return Array array($status, $response)
     */
    public function log($log)
    {
        list($status, $response) = $this->send($log);
        
        return array($status, $response);
    }
    public function send($log) {
        static::$logs[] = $log;
    }
    public function sendVerdade($log)
    {
        $url = "http://{$this->_host}:{$this->_port}/log?" . http_build_query($log);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return array($status, $response);
    }
    static function getip() {
        $realip = '0.0.0.0';
        if ($_SERVER) {
            if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } elseif ( isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER["HTTP_CLIENT_IP"] ) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if ( getenv('HTTP_X_FORWARDED_FOR') ) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif ( getenv('HTTP_CLIENT_IP') ) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        return $realip;
    }
    public static function getErrorName($err)
    {
        $errors = array(
            E_ERROR             => 'ERROR',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_WARNING           => 'WARNING',
            E_PARSE             => 'PARSE',
            E_NOTICE            => 'NOTICE',
            E_STRICT            => 'STRICT',
            E_DEPRECATED        => 'DEPRECATED',
            E_CORE_ERROR        => 'CORE_ERROR',
            E_CORE_WARNING      => 'CORE_WARNING',
            E_COMPILE_ERROR     => 'COMPILE_ERROR',
            E_COMPILE_WARNING   => 'COMPILE_WARNING',
            E_USER_ERROR        => 'USER_ERROR',
            E_USER_WARNING      => 'USER_WARNING',
            E_USER_NOTICE       => 'USER_NOTICE',
            E_USER_DEPRECATED   => 'USER_DEPRECATED',
        );
        return $errors[$err];
    }
    private static function set_error_handler($nodeHost, $nodePort)
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) use($nodeHost, $nodePort) {
            $err = NodeLog::getErrorName($errno);
            /*
            if (!(error_reporting() & $errno)) {
                // This error code is not included in error_reporting
                return;
            } 
            */

            $log = array(
                'hostname' => $_SERVER['APP_HOSTNAME'],
                'site_name' => $_SERVER['APP_NAME'],
                'title'=>"<b class='{$err}'>{$err}</b> {$errfile}:{$errline}",
                'msg'=>nl2br($errstr),
                "codigo" => $errno,
                'message' => $errstr,
                'file' => $errfile,
                'line' => $errline,
                'type' => $err
            );
            NodeLog::connect($nodeHost, $nodePort)->log($log);
            return false;
        });
    }
    private static function register_exceptionHandler($nodeHost, $nodePort)
    {
        set_exception_handler(function($exception) use($nodeHost, $nodePort) {
            $exceptionName = get_class($exception);
            $message = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();
            $trace = $exception->getTraceAsString();
            $msg = count($trace) > 0 ? "Stack trace:\n{$trace}" : null;
            $log = array(
                'hostname' => $_SERVER['APP_HOSTNAME'],
                'site_name' => $_SERVER['APP_NAME'],
                'title'=>nl2br("<b class='ERROR'>Uncaught exception '{$exceptionName}'</b> with message '{$message}' in {$file}:{$line}"),
                'msg'=>nl2br($msg),
                'codigo' => $exception->getCode(),
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'type' => 'ERROR'
            );
            NodeLog::connect($nodeHost, $nodePort)->log($log);
            return false;
        });
    }
    private static function register_shutdown_function($nodeHost, $nodePort)
    {
        register_shutdown_function(function() use($nodeHost, $nodePort) {
            session_write_close();
            $error = error_get_last();
             if ($error) {
                $err = NodeLog::getErrorName($error['type']);
                $log = array(
                    'hostname' => $_SERVER['APP_HOSTNAME'],
                    'site_name' => $_SERVER['APP_NAME'],
                    'title'=>"<b class='{$err}'>{$err}</b> {$error['file']}:{$error['line']}",
                    'msg'=>nl2br($error['message']),
                    'codigo' => $error['type'],
                    'message' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'type' => $err
                );
                NodeLog::connect($nodeHost, $nodePort)->log($log);
            }

            NodeLog::connect($nodeHost, $nodePort)->end();
            

            foreach(NodeLog::$logs as $log) {
                 NodeLog::connect($nodeHost, $nodePort)->sendVerdade($log);
            }

            
        });
    }
    private static $_defHost = self::NODE_DEF_HOST;
    private static $_defPort = self::NODE_DEF_PORT;
    /**
     * @param String $host
     * @param Integer $port
     * @return NodeLog
     */

    private static $name = '';
    private static $hostname = '';
    public static function init($host = self::NODE_DEF_HOST, $port = self::NODE_DEF_PORT)
    {
        self::$_defHost = $host;
        self::$_defPort = $port;

        $app = Config::get('app');
        $_SERVER['APP_HOSTNAME'] = $_SERVER['SERVER_NAME'].' ('.$app->name.')';
        $_SERVER['APP_NAME'] = $app->name;
        
        
        self::register_exceptionHandler($host, $port);
        self::set_error_handler($host, $port);
        self::register_shutdown_function($host, $port);
        $node = self::connect($host, $port);
        $node->start();
        return $node;
    }
    private static $time;
    private static $mem;
    private static $uri;
    public function start()
    {
        return;
        self::$time = microtime(TRUE);
        self::$mem = memory_get_usage();
        static::$uri = $_SERVER['REQUEST_URI'];
        $uri = static::$uri;
        $log = array(
            'hostname' => $_SERVER['APP_HOSTNAME'],
            'site_name' => $_SERVER['APP_NAME'],
            'title'=>"<b class='OK'>Start</b> >>>> {$uri}",
            'msg' => '',
            'codigo' => 0,
            'message' => "Start >>>> {$uri}",
            'file' => '',
            'line' => '',
            'type' => 'OK'
        );
        $this->log($log);
    }
    public function end()
    {
        return;
        $uri = static::$uri;
        $mem = (memory_get_usage() - self::$mem) / (1024 * 1024);
        $time = microtime(TRUE) - self::$time;
        $log = array(
            'hostname' => $_SERVER['APP_HOSTNAME'],
            'site_name' => $_SERVER['APP_NAME'],
            'title'=>"<b class='OK'>End</b> <<<< {$uri} mem: {$mem} time {$time}",
            'msg' => '',
            'codigo' => 0,
            'message' => "End <<<< {$uri} mem: {$mem} time {$time}",
            'file' => '',
            'line' => '',
            'type' => 'OK'
        );
        $this->log($log);
    }
}