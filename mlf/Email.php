<?php
/**
 * Date class for sending e-mails with attachments.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Email
{
    /**
     * To email.
     *
     * @var string
     */
    protected $_to = '';
    /**
     * From email.
     *
     * @var string
     */
    protected $_from = '';
    /**
     * Reply to email.
     *
     * @var string
     */
    protected $_replyTo = '';
    /**
     * E-mail headers.
     *
     * @var array
     */
    protected $_headers = array();
    /**
     * E-mail body.
     *
     * @var string
     */
    protected $_body = '';
    /**
     * E-mail html body.
     *
     * @var string
     */
    protected $_htmlBody = '';
    /**
     * E-mail subject.
     *
     * @var string
     */
    protected $_subject = '';
    /**
     * E-mail attachments.
     *
     * @var array
     */
    protected $_attachments = array();
    /**
     * E-mail hash.
     *
     * <p>Needed for attachments.
     *
     * @var string
     */
    protected $_hash = '';
    /**
     * E-mail main hash.
     *
     * <p>Needed for attachments.
     *
     * @var string
     */
    protected $_mainHash = '';
    /**
     * Constructor of the email instance.
     *
     * @return null
     */
    public function __construct()
    {
        $this->_hash = md5(uniqid());
        $this->_mainHash = md5(uniqid());
    }
    /**
     * Set to address.
     *
     * @param string $to
     * @return null
     */
    public function setTo($to)
    {
        $this->_to = $to;
    }
    /**
     * Set from address.
     *
     * @param string $from
     * @return null
     */
    public function setFrom($from)
    {
        $this->_from = $from;
    }
    /**
     * Set reply to address.
     *
     * @param string $replyTo
     * @return null
     */
    public function setReplyTo($replyTo)
    {
        $this->_replyTo = $replyTo;
    }
    /**
     * Set subject.
     *
     * @param string $subject
     * @return null
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
    }
    /**
     * Set body.
     *
     * @param string $body
     * @return null
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }
    /**
     * Set html body.
     *
     * @param string $htmlBody
     * @return null
     */
    public function setHtmlBody($htmlBody)
    {
        $this->_htmlBody = $htmlBody;
    }
    /**
     * Set header.
     *
     * @param string $key
     * @param string $value
     * @return null
     */
    public function setHeader($key, $value)
    {
        $this->_headers[] = "{$key}: {$value}";
    }
    /**
     * Set attachment.
     *
     * @param string $name
     * @param string $content
     * @return null
     */
    public function setAttachment($name, $content)
    {
        $this->_attachments[$name] = $content;
    }
    /**
     * Send the e-mail.
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return bool TRUE if the mail was successfully accepted for delivery, FALSE otherwise.
     * @throws Exception In case to, subject or body is missing.
     */
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
    /**
     * Prepares the headers to attach to the e-mail.
     *
     * @return string
     */
    protected function _prepareHeaders()
    {
        return implode("\r\n", $this->_headers);
    }
    /**
     * Prepares the body to attach to the e-mail.
     *
     * <p>Also includes the attachments.
     *
     * @return string
     */
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