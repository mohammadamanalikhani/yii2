<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\form;

use Yii;
use yii\base\Model;
use yii\gii\CodeFile;

/**
 * This generator will generate an action view file based on the specified model class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public $modelClass;
	public $viewPath = '@app/views';
	public $viewName;
	public $scenarioName;


	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Form Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'This generator generates a view script file that displays a form to collect input for the specified model class.';
	}

	/**
	 * @inheritdoc
	 */
	public function generate()
	{
		$files = array();
		$files[] = new CodeFile(
			Yii::getAlias($this->viewPath) . '/' . $this->viewName . '.php',
			$this->render($this->getTemplatePath() . '/form.php')
		);
		return $files;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('modelClass, viewName, scenarioName, viewPath', 'filter', 'filter' => 'trim'),
			array('modelClass, viewName, viewPath', 'required'),
			array('modelClass', 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'),
			array('modelClass', 'validateModel'),
			array('viewName', 'match', 'pattern' => '/^\w+[\\-\\/\w]*$/', 'message' => 'Only word characters, dashes and slashes are allowed.'),
			array('viewPath', 'match', 'pattern' => '/^@?\w+[\\-\\/\w]*$/', 'message' => 'Only word characters, dashes, slashes and @ are allowed.'),
			array('viewPath', 'validateViewPath'),
			array('scenarioName', 'match', 'pattern' => '/^[\w\\-]+$/', 'message' => 'Only word characters and dashes are allowed.'),
		));
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return array(
			'modelClass' => 'Model Class',
			'viewName' => 'View Name',
			'viewPath' => 'View Path',
			'scenarioName' => 'Scenario',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function requiredTemplates()
	{
		return array(
			'form.php',
			'action.php',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return array('viewPath', 'scenarioName');
	}

	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return array(
			'modelClass' => 'This is the model class for collecting the form input. You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
			'viewName' => 'This is the view name with respect to the view path. For example, <code>site/index</code> would generate a <code>site/index.php</code> view file under the view path.',
			'viewPath' => 'This is the root view path to keep the generated view files. You may provide either a directory or a path alias, e.g., <code>@app/views</code>.',
			'scenarioName' => 'This is the scenario to be used by the model when collecting the form input. If empty, the default scenario will be used.',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function successMessage()
	{
		$code = highlight_string($this->render($this->getTemplatePath() . '/action.php'), true);
		return <<<EOD
<p>The form has been generated successfully.</p>
<p>You may add the following code in an appropriate controller class to invoke the view:</p>
<pre>$code</pre>
EOD;
	}

	/**
	 * Validates the model class to make sure it exists and is valid.
	 */
	public function validateModel()
	{
		try {
			if (class_exists($this->modelClass)) {
				if (!is_subclass_of($this->modelClass, Model::className())) {
					$this->addError('modelClass', "'{$this->modelClass}' must extend from Model or its child class.");
				}
			} else {
				$this->addError('modelClass', "Class '{$this->modelClass}' does not exist or has syntax error.");
			}
		} catch (\Exception $e) {
			$this->addError('modelClass', "Class '{$this->modelClass}' does not exist or has syntax error.");
			return;
		}
	}

	/**
	 * Validates [[viewPath]] to make sure it is a valid path or path alias and exists.
	 */
	public function validateViewPath()
	{
		$path = Yii::getAlias($this->viewPath, false);
		if ($path === false || !is_dir($path)) {
			$this->addError('viewPath', 'View path does not exist.');
		}
	}

	/**
	 * @return array list of safe attributes of [[modelClass]]
	 */
	public function getModelAttributes()
	{
		/** @var Model $model */
		$model = new $this->modelClass;
		if (!empty($this->scenarioName)) {
			$model->setScenario($this->scenarioName);
		}
		return $model->safeAttributes();
	}
}
