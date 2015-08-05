<?php
/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
/** --------------------------
class UserIdentity extends CUserIdentity
{
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.

	public function authenticate()
	{
		$users=array(
			// username => password
			'demo'=>'demo',
			'admin'=>'admin',
		);
		if(!isset($users[$this->username]))
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		elseif($users[$this->username]!==$this->password)
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
			$this->errorCode=self::ERROR_NONE;
		return !$this->errorCode;
	}
}
 *
 *
 *
 *
 *
 *
 * --------------------------add db table user mysql
 */

class UserIdentity extends CUserIdentity
{
	private $_id;

	public function authenticate()
	{
		$username=strtolower($this->username);
		$user=User::model()->find('LOWER(username)=?',array($username));
		if($user===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if(!$user->validatePassword($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$user->id;
			$this->username=$user->username;
			$this->errorCode=self::ERROR_NONE;
		}
		return $this->errorCode==self::ERROR_NONE;
	}

	public function getId()
	{
		return $this->_id;
	}
}

/**
 *В методе authenticate() мы используем класс User для поиска
 * строки в таблице tbl_user, в которой значение поля username
 * такое же, как полученное имя пользователя без учета регистра.
 * Помните, что класс User был создан, используя инструмент gii в предыдущем разделе.
 * Поскольку класс User наследуется от класса CActiveRecord,
 * мы можем использовать возможности ActiveRecord для того,
 * чтобы обращаться к таблице tbl_user в ОО манере.
 *
 * Для того, чтобы проверить, ввёл ли пользователь правильный пароль,
 * мы вызываем метод validatePassword класса User.
 * Нам необходимо изменить файл /wwwroot/blog/protected/models/User.php как показано ниже.
 * Отметим, что вместо хранения пароля в БД в явном виде, мы сохраняем его хеш.
 * При проверке введённого пользователем пароля, вместо сравнения паролей, мы должны сравнивать хеши.
 * хеширования пароля и его проверки мы используем входящий в Yii класс CPasswordHelper.
 *http://www.yiiframework.ru/doc/blog/ru/prototype.auth
 */

