<?php
/**
 * Auth class.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Auth
{
    /**
     * Sign in route.
     */
    const SINGIN_URL = 'account/singin';
    /**
     * The instance of Auth.
     *
     * @var Auth
     */
    private static $_instance;
    /**
     * Sign in flag.
     *
     * @var boolean|null
     */
    private $_singedin = null;
    /**
     * Holds the user.
     *
     * @var Model_User|null
     */
    private $_user = null;
    /**
     * Returns the instance of the Auth.
     *
     * @return Auth
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof Auth)) {
            self::$_instance = new Auth();
        }
        return self::$_instance;
    }
    /**
     * Checks if the user is signed in.
     *
     * @return boolean
     */
    public function isSingedIn()
    {
        if ($this->_singedin === null) {
            $user = $this->getUser();
            if (!empty($user->userId)) {
                $sessionId = uniqid();
                $user->updateSession($this->_getSessionId(), $sessionId);
                $this->_setSessionId($sessionId);
                $this->_singedin = true;
            } else {
                $this->_singedin = false;
            }
        }
        return $this->_singedin;
    }
    /**
     * Processes the sign in.
     *
     * @param Model_User $user
     * @param string $sessionId
     * @return null
     */
    public function singIn(Model_User $user, $sessionId)
    {
        $this->_setSessionId($sessionId);
    }
    /**
     * Processes the sign out.
     *
     * @return null
     */
    public function singOut()
    {
        Request::getInstance()->cookies['sessionId'] = null;
        setcookie('sessionId', '', strtotime('-2 weeks'), '/');
    }
    /**
     * Returns the currently signed in user.
     *
     * @return Model_User
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Model_User::getBySessionId($this->_getSessionId());
        }
        return $this->_user;
    }
    /**
     * Sets the sessionId.
     *
     * @param string $sessionId
     * @return null
     */
    private function _setSessionId($sessionId)
    {
        Request::getInstance()->cookies['sessionId'] = $sessionId;
        setcookie('sessionId', $sessionId, strtotime('+2 weeks'), '/');
    }
    /**
     * Returns the sessionId.
     *
     * @return string
     */
    private function _getSessionId()
    {
        $cookies = Request::getInstance()->cookies;
        $sessionId = '';
        if (isset($cookies['sessionId'])) {
            $sessionId = $cookies['sessionId'];
        }
        return $sessionId;
    }
}