<?php
Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl.'/js/Options.js');

$menu = array(
	array('label'=>'Email', 'url'=>'#', 'linkOptions'=>array(
		'ajax'=>array(
			'type'=>'GET',
			'url'=>'',
			'update'=>'#option',
			'data'=>array('Email'=>true)
		),
		'class'=>'category'
	)),
	array('label'=>'Password', 'url'=>'#', 'linkOptions'=>array(
		'ajax'=>array(
			'type'=>'GET',
			'url'=>'',
			'update'=>'#option',
			'data'=>array('Password'=>true)
		),
		'class'=>'category'
	)),
	array('label'=>'Tags', 'url'=>'#', 'linkOptions'=>array(
		'ajax'=>array(
			'type'=>'GET',
			'url'=>'',
			'update'=>'#option',
			'data'=>array('Tags'=>true)
		),
		'class'=>'category'
	)),
);
if(Yii::app()->authManager->checkAccess('Admin', Yii::app()->user->getId()))
	$menu = $this->adminOptions($menu);

$this->menu = $menu;
?>

<?php echo CHtml::image(YiiIdenticon::getImageDataUri($model->id), $model->username); ?>
<h1>Options</h1>
<hr>
<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'htmlOptions'=>array('class'=>'table table-striped'),
	'attributes'=>array(
		'username',
		'email'
	),
)); ?>
<hr>
<div id="option"></div>