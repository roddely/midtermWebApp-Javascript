<?php
namespace PHPMailer\PHPMailer;

class SMTP
{
    const VERSION = '6.8.1';
    const CRLF = "\r\n";
    const DEFAULT_SMTP_PORT = 25;
    const MAX_LINE_LENGTH = 998;
    const DEBUG_OFF = 0;
    const DEBUG_CLIENT = 1;
    const DEBUG_SERVER = 2;
    const DEBUG_CONNECTION = 3;
    const DEBUG_LOWLEVEL = 4;

    protected $do_debug = self::DEBUG_OFF;
    protected $Debugoutput = 'echo';
    protected $do_verp = false;
    protected $Timeout = 300;
    protected $Timelimit = 300;
    protected $smtp_conn;
    protected $error = ['error' => '', 'detail' => '', 'smtp_code' => '', 'smtp_code_ex' => ''];
    protected $helo_rply = '';
    protected $server_caps = [];
    protected $last_reply = '';

    public function connect($host, $port = null, $timeout = 30)
    {
        $this->error = ['error' => '', 'detail' => '', 'smtp_code' => '', 'smtp_code_ex' => ''];
        
        if ($this->connected()) {
            $this->error = ['error' => 'Already connected to a server'];
            return false;
        }

        if (empty($port)) {
            $port = self::DEFAULT_SMTP_PORT;
        }

        $this->smtp_conn = @fsockopen(
            $host,
            $port,
            $errno,
            $errstr,
            $timeout
        );

        if (empty($this->smtp_conn)) {
            $this->error = ['error' => 'Failed to connect to server',
                          'detail' => $errstr,
                          'smtp_code' => $errno];
            return false;
        }

        return true;
    }

    public function startTLS()
    {
        if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
            return false;
        }

        if (!stream_socket_enable_crypto(
            $this->smtp_conn,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT
        )) {
            return false;
        }

        return true;
    }

    public function authenticate($username, $password)
    {
        $this->sendCommand('AUTH LOGIN', 'AUTH LOGIN', 334);
        $this->sendCommand(base64_encode($username), base64_encode($username), 334);
        return $this->sendCommand(base64_encode($password), base64_encode($password), 235);
    }

    public function connected()
    {
        if (is_resource($this->smtp_conn)) {
            $sock_status = stream_get_meta_data($this->smtp_conn);
            if ($sock_status['eof']) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function hello($host = '')
    {
        return $this->sendCommand('EHLO ' . $host, 'EHLO ' . $host, 250);
    }

    public function mail($from)
    {
        return $this->sendCommand('MAIL FROM:<' . $from . '>', 'MAIL FROM:<' . $from . '>', 250);
    }

    public function recipient($address)
    {
        return $this->sendCommand('RCPT TO:<' . $address . '>', 'RCPT TO:<' . $address . '>', [250, 251]);
    }

    public function data($msg_data)
    {
        if (!$this->sendCommand('DATA', 'DATA', 354)) {
            return false;
        }

        $msg_data = str_replace("\r\n.", "\r\n..", $msg_data);
        $msg_data = substr($msg_data, 0, -2);
        $msg_data .= self::CRLF . '.' . self::CRLF;

        return $this->sendCommand($msg_data, '', 250);
    }

    public function quit()
    {
        return $this->sendCommand('QUIT', 'QUIT', 221);
    }

    protected function sendCommand($command, $commandstring, $expect)
    {
        if (!$this->connected()) {
            $this->error = ['error' => 'Called sendCommand() without being connected'];
            return false;
        }

        fputs($this->smtp_conn, $command . self::CRLF);

        $reply = $this->getLines();
        $code = substr($reply, 0, 3);

        if (!is_array($expect)) {
            $expect = [$expect];
        }

        if (!in_array($code, $expect)) {
            $this->error = ['error' => "$command command failed",
                          'smtp_code' => $code,
                          'detail' => substr($reply, 4)];
            return false;
        }

        $this->last_reply = $reply;
        return true;
    }

    protected function getLines()
    {
        $data = '';
        while ($str = fgets($this->smtp_conn, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) == ' ') {
                break;
            }
        }
        return $data;
    }
} 