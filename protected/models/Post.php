<?php

/**
 * This is the model class for table "tbl_post".
 *
 * The followings are the available columns in table 'tbl_post':
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $tags
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $author_id
 *
 * The followings are the available model relations:
 * @property Comment[] $comments
 * @property User $author
 */
class Post extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'tbl_post';
	}


	/**
	 *Для того, чтобы сделать код более читаемым мы описываем константы,
	 * соответствующие целочисленным значениям статуса.
	 * Эти константы необходимо использовать в коде вместо соответствующих им целых значений.
	 */
	const STATUS_DRAFT=1;
	const STATUS_PUBLISHED=2;
	const STATUS_ARCHIVED=3;




	/**
	 *Добавляем свойство url
	 * 	Каждой записи соответствует уникальный URL.
	 * Вместо повсеместного вызова CWebApplication::createUrl для формирования этого URL,
	 * мы можем добавить свойство url модели Post и повторно использовать код для генерации URL.
	 * Позже мы опишем, как получить красивые URL.
	 * Использование свойства модели позволит реализовать это максимально удобно.
	 * 	Для того, чтобы добавить свойство url, мы добавляем геттер в класс Post:
	 */
	public function getUrl()
	{
		return Yii::app()->createUrl('post/view', array(
			'id'=>$this->id,
			'title'=>$this->title,
		));
	}






	/**
	 * @return array validation rules for model attributes.
	 *
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title, content, status, author_id', 'required'),
			array('status, create_time, update_time, author_id', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>128),
			array('tags', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, title, content, tags, status, create_time, update_time, author_id', 'safe', 'on'=>'search'),
		);
	}
*/



	public function rules()
	{
		return array(
			array('title, content, status', 'required'),
			array('title', 'length', 'max'=>128),
			array('status', 'in', 'range'=>array(1,2,3)),
			array('tags', 'match', 'pattern'=>'/^[\w\s,]+$/',
				'message'=>'В тегах можно использовать только буквы.'),
			array('tags', 'normalizeTags'),

			array('title, status', 'safe', 'on'=>'search'),
		);
	}





	public function normalizeTags($attribute,$params)
	{
		$this->tags=Tag::array2string(array_unique(Tag::string2array($this->tags)));
	}












	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'comments' => array(self::HAS_MANY, 'Comment', 'post_id'),
			'author' => array(self::BELONGS_TO, 'User', 'author_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Title',
			'content' => 'Content',
			'tags' => 'Tags',
			'status' => 'Status',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
			'author_id' => 'Author',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('tags',$this->tags,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);
		$criteria->compare('author_id',$this->author_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Post the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


	/*
 *
 * Далее изменим класс Post таким образом,
 * чтобы он автоматически выставлял некоторые атрибуты
 * (такие, как create_time и author_id) непосредственно перед сохранением записи в БД.
 * Перекроем метод beforeSave():
 *
 *
 */

	protected function beforeSave()
	{
		if(parent::beforeSave())
		{
			if($this->isNewRecord)
			{
				$this->create_time=$this->update_time=time();
				$this->author_id=Yii::app()->user->id;
			}
			else
				$this->update_time=time();
			return true;
		}
		else
			return false;
	}



	/*
    *При сохранении записи мы хотим также обновить информацию о частоте использования тегов в таблице tbl_tag.
	 * Мы можем реализовать это в методе afterSave(), который автоматически вызывается после успешного сохранения записи в БД.
    *
    */

	protected function afterSave()
	{
		parent::afterSave();
		Tag::model()->updateFrequency($this->_oldTags, $this->tags);
	}

	private $_oldTags;


	/*
	*
	 *Так как необходимо определить, менял ли пользователь теги при редактировании записи, нам понадобятся старые теги.
	 * Для этого мы реализуем метод afterFind(), который записывает старые теги в свойство _oldTags.
	 * Метод afterFind() вызывается автоматически при заполнении модели AR данными, полученными из БД.
	 *
	 *
	*/

	protected function afterFind()
	{
		parent::afterFind();
		$this->_oldTags=$this->tags;
	}

}

