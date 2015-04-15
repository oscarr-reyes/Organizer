<?php 

class UserController extends Controller
{
	public function filters()
	{
		return array(
			'accessControl',
			'postOnly + delete'
		);
	}

	public function accessRules()
	{
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('view'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	public function actionView($id)
	{
		if(isset($_GET['Email']))
		{
			$model = $this->loadModel($id);

			if(isset($_POST['User']['email']))
			{
				$model->attributes = $_POST['User'];

				if($model->save())
				{
					$this->redirect(array('view',
						'id'=>$model->id
					));
				}
			}

			if(isset($_POST['User']['emailNotification']))
			{
				$model->attributes = $_POST['User'];

				$model->ProjectNotification = $_POST['User']['ProjectNotification'];
				$model->NewTaskNotification = $_POST['User']['NewTaskNotification'];
				$model->UpdatedTaskNotification = $_POST['User']['UpdatedTaskNotification'];
				$model->CommentedTaskNotification = $_POST['User']['CommentedTaskNotification'];

				if($model->save())
				{
					$this->redirect(array('view',
						'id'=>$model->id
					));
				}
			}

			$this->renderPartial('Email', array(
				'model'=>$model,
				'checked'=>User::model()->ListNotification($id)
			));
			Yii::app()->end();
		}

		if(isset($_GET['Password']))
		{
			echo "Password";
			Yii::app()->end();
		}

		if(isset($_GET['Tags']))
		{
			echo "Tags";
			Yii::app()->end();
		}

		$model = $this->loadModel($id);

		$this->layout = '//layouts/column3';

		$this->render('view', array(
			'model'=>$model,
		));
	}

	private function loadModel($id)
	{
		$model = User::model()->findByPk($id);

		if($model === null)
			throw new CHttpException(404, 'the requested page does not exist.');

		return $model;
	}

}