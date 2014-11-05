<?php
class Controller_Account extends Controller_Abstract
{
    public function singout()
    {
        Auth::getInstance()->singOut();
        $this->_redirect('/');
    }

    public function singin()
    {
        if ($this->_getRequest()->isPost()) {
            $user = Model_User::getByEmailAndPassword(
                $this->_getRequest()->post['email'],
                $this->_getRequest()->post['password']
            );
            if (!empty($user->userId)) {
                $sessionId = uniqid();
                $user->newSession($sessionId, $_SERVER['REMOTE_ADDR']);
                Auth::getInstance()->singIn($user, $sessionId);
                $this->_redirect('/');
            }
        }
        return $this->getView()->render('singin');
    }
}