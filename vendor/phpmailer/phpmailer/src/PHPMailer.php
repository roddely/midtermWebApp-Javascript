<?php
namespace PHPMailer\PHPMailer;

class PHPMailer
{
    const CHARSET_ASCII = 'us-ascii';
    const CHARSET_ISO88591 = 'iso-8859-1';
    const CHARSET_UTF8 = 'utf-8';

    const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    const CONTENT_TYPE_TEXT_HTML = 'text/html';
    const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';

    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';

    public $Priority;
    public $CharSet = self::CHARSET_UTF8;
    public $ContentType = self::CONTENT_TYPE_PLAINTEXT;
    public $Encoding = self::ENCODING_8BIT;
    public $ErrorInfo = '';
    public $From = '';
    public $FromName = '';
    public $Sender = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $Mailer = 'smtp';
    public $Host = '';
    public $Port = 25;
    public $SMTPSecure = '';
    public $SMTPAutoTLS = true;
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $Recipients = [];
    public $smtp;
    public $SMTPDebug = 0;
    public $Debugoutput = 'echo';

    public function __construct($exceptions = null)
    {
        if (null !== $exceptions) {
            $this->exceptions = (bool)$exceptions;
        }
    }

    public function isHTML($isHtml = true)
    {
        if ($isHtml) {
            $this->ContentType = static::CONTENT_TYPE_TEXT_HTML;
        } else {
            $this->ContentType = static::CONTENT_TYPE_PLAINTEXT;
        }
    }

    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    public function setFrom($address, $name = '')
    {
        $this->From = $address;
        $this->FromName = $name;
    }

    public function addAddress($address)
    {
        $this->Recipients[] = $address;
    }

    public function send()
    {
        if ($this->SMTPAuth) {
            $this->smtp = new SMTP;
            
            if ($this->SMTPSecure == self::ENCRYPTION_STARTTLS) {
                if (!$this->smtp->connect($this->Host, $this->Port)) {
                    throw new Exception('SMTP connection failed');
                }
                if (!$this->smtp->hello(gethostname())) {
                    throw new Exception('SMTP HELO failed');
                }
                if (!$this->smtp->startTLS()) {
                    throw new Exception('SMTP STARTTLS failed');
                }
                if (!$this->smtp->authenticate($this->Username, $this->Password)) {
                    throw new Exception('SMTP authentication failed');
                }
            }

            foreach ($this->Recipients as $recipient) {
                if (!$this->smtp->mail($this->From)) {
                    throw new Exception('SMTP FROM failed');
                }
                if (!$this->smtp->recipient($recipient)) {
                    throw new Exception('SMTP TO failed');
                }
                if (!$this->smtp->data($this->createHeader() . $this->Body)) {
                    throw new Exception('SMTP DATA failed');
                }
            }

            $this->smtp->quit();
            return true;
        }

        return mail(
            implode(', ', $this->Recipients),
            $this->Subject,
            $this->Body,
            $this->createHeader()
        );
    }

    protected function createHeader()
    {
        $header = [];
        $header[] = 'MIME-Version: 1.0';
        $header[] = 'Content-Type: ' . $this->ContentType . '; charset=' . $this->CharSet;
        $header[] = 'From: ' . $this->FromName . ' <' . $this->From . '>';
        
        return implode("\r\n", $header) . "\r\n\r\n";
    }
} 