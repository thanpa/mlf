<?php
class Model_User extends Model_Abstract
{
    public function save()
    {
        $data = array();
        foreach (Table_Users::getInstance()->getFields() as $field) {
            if (isset($this->$field)) {
                $data[$field] = $this->$field;
            }
        }
        if (!empty($this->userId)) {
            Table_Users::getInstance()->update($data, array('userId' => $this->userId));
        } else {
            $this->userId = Table_Users::getInstance()->insert($data);
        }
        return $this;
    }
    public function newSession($sessionid, $ip)
    {
        return Table_UserSessions::getInstance()->insert(
            array(
                'userId' => $this->userId,
                'sessionId' => $sessionid,
                'ip' => $ip,
            )
        );
    }
    public function updateSession($oldSessionId, $newSessionId)
    {
        return Table_UserSessions::getInstance()->update(
            array('sessionId' => $newSessionId),
            array('userId' => $this->userId, 'sessionId' => $oldSessionId)
        );
    }
    public static function getBySessionId($sessionId)
    {
        $user = Table_Users::getInstance()->query(
            "
                SELECT users.* FROM users
                LEFT JOIN userSessions on users.userId = userSessions.userId
                WHERE userSessions.sessionId = '{$sessionId}'
            "
        );
        if ($user !== null) {
            $result = parent::gen($user, new Model_User());
        } else {
            $result = null;
        }
        return $result;
    }
    public static function getByEmailAndPassword($email, $password)
    {
        return parent::gen(
            Table_Users::getInstance()->select(array('email' => $email, 'password' => Security::hash($password))),
            new Model_User()
        );
    }
}