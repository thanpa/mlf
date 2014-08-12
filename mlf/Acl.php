<?php
/**
 * Acl class.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Acl
{
    /**
     * The default route.
     */
    const DEFAULT_URL = 'default/index';
    /**
     * The instance of Acl.
     *
     * @var Acl
     */
    private static $_instance;
    /**
     * List of admins (emails).
     *
     * @var array
     */
    private $_admins = array();
    /**
     * List of other users.
     *
     * @var array
     */
    private $_acl = array(
        'user1@test.com' => '*',
        'user2@test.com' => array(
            'default/index',
            'account/logout',
        ),
        'user3@test.com' => array(
            'controller/action',
            'controller/action',
            'controller/action',
            'account/logout',
        ),
        '' => array(),
    );
    /**
     * Returns the instance of the Acl.
     *
     * @return Acl
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof Acl)) {
            self::$_instance = new Acl();
        }
        return self::$_instance;
    }
    /**
     * Checks if the route is allowed for the email provided.
     *
     * The method will allow any url in case the call is from cli.
     *
     * @param string $url
     * @param string $email
     * @return boolean
     */
    public function isAllowed($url, $email = null)
    {
        if ($email === null) {
            $email = Auth::getInstance()->getUser()->email;
        }
        $result = false;
        if (php_sapi_name() === 'cli') {
            $result = true;
        }
        if (in_array($url, $this->_acl[$email])) {
            $result = true;
        }
        if (in_array($email, $this->_admins)) {
            $result = true;
        }
        return $result;
    }
}