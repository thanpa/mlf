<?php
class Email
{
    protected $_to = '';
    protected $_from = '';
    protected $_replyTo = '';
    protected $_headers = array();
    protected $_body = '';
    protected $_htmlBody = '';
    protected $_subject = '';
    protected $_attachments = array();
    protected $_hash = '';
    protected $_mainHash = '';
    public function __construct()
    {
        $this->_hash = md5(uniqid());
        $this->_mainHash = md5(uniqid());
    }
    public function setTo($to)
    {
        $this->_to = $to;
    }
    public function setFrom($from)
    {
        $this->_from = $from;
    }
    public function setReplyTo($replyTo)
    {
        $this->_replyTo = $replyTo;
    }
    public function setSubject($subject)
    {
        $this->_subject = $subject;
    }
    public function setBody($body)
    {
        $this->_body = $body;
    }
    public function setHtmlBody($htmlBody)
    {
        $this->_htmlBody = $htmlBody;
    }
    public function setHeader($key, $value)
    {
        $this->_headers[] = "{$key}: {$value}";
    }
    public function setAttachment($name, $content)
    {
        $this->_attachments[$name] = $content;
    }
    public function send($to = null, $subject = null, $body = null)
    {
        if (!empty($to)) {
            $this->setTo($to);
        }
        if (empty($this->_to)) {
            throw new Exception('Where are you sending this email?');
        }
        if (!empty($subject)) {
            $this->setSubject($subject);
        }
        if (empty($this->_subject)) {
            throw new Exception('Please provide a subject');
        }
        if (!empty($body)) {
            $this->setBody($body);
        }
        if (empty($this->_body)) {
            throw new Exception('The body is empty!');
        }
        if (!empty($this->_from)) {
            $this->setHeader('From', $this->_from);
        }
        if (!empty($this->_replyTo)) {
            $this->setHeader('Reply-To', $this->_replyTo);
        }
        if (!empty($this->_attachments)) {
            $this->setHeader('Content-Type', sprintf('multipart/mixed; boundary=%s', $this->_mainHash));
        }
        $body = $this->_prepareBody();
        return mail($this->_to, $this->_subject, $body, $this->_prepareHeaders());
    }
    protected function _prepareHeaders()
    {
        return implode("\r\n", $this->_headers);
    }
    protected function _prepareBody()
    {
        $attachments = array();
        foreach ($this->_attachments as $name => $attachment) {
            $attachments[$name] = chunk_split(base64_encode(file_get_contents($attachment)));
        }
        $body = sprintf(
            "--%1\$s\r\nContent-Type: multipart/alternative; boundary=%2\$s\r\n\r\n--%2\$s\r\nContent-Type: text/plain; charset=\"iso-8859-1\"\r\nContent-Transfer-Encoding: 7bit\r\n\r\n%3\$s\r\n\r\n--%2\$s\r\nContent-Type: text/html; charset=\"iso-8859-1\"\r\nContent-Transfer-Encoding: 7bit\r\n\r\n%4\$s\r\n\r\n--%2\$s--\r\n",
            $this->_mainHash,
            $this->_hash,
            $this->_body,
            $this->_htmlBody
        );
        foreach ($attachments as $name => $attachment) {
            $body .= sprintf(
                "--%1\$s\r\nContent-Type: application/zip; name=\"%2\$s\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment\r\n\r\n%3\$s",
                $this->_mainHash,
                $name,
                $attachment
            );
        }
        $body .= sprintf('--%1$s--', $this->_mainHash);
        return $body;
    }
}