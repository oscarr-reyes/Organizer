<?php /* @var $this SiteController */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<!-- blueprint CSS framework -->
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/dist/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/dist/css/offcanvas.css" />
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/JQuery.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/text.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/sound.js"></script>

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>

	<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<a class="navbar-brand" href="#"><?php echo CHtml::encode(Yii::app()->name); ?></a>
				<p class="navbar-text">[Channel tabs HERE!]</p>
			</div>
		</div>
	</div>
	<div id="chatBox">
	</div>

	<div class="navbar navbar-inverse" id="chatMenu">
		<div class="navbar-header">
			<p class="navbar-text" id="Username"><?php echo CHtml::image(YiiIdenticon::getImageDataUri(Yii::app()->user->getId(), '20'), '').' '.Yii::app()->user->name; ?></p>
			<p class="navbar-text">[Icons and options HERE!]</p>
		</div>
		<?php echo CHtml::beginForm(null, 'post', array('id'=>'messageForm')); ?>
			<div class="form-group col-md-12">
				<?php echo CHtml::textArea('Message', '', array(
					'class'=>'form-control',
					'placeholder'=>'Message',
					'autoComplete'=>'false',
					'id'=>'Message'
				)); ?>
			</div>
		<?php echo CHtml::endForm(); ?>
	</div>

	<!-- Bootstrap core JavaScript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/offcanvas.js"></script>
	<script src="http://<?php echo Yii::app()->request->serverName; ?>:3000/socket.io/socket.io.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/dist/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		if(typeof io === 'undefined')
		{
			$('#Message').attr('placeholder', 'Cannot connect to server...');
			$('#Message').attr('disabled', true);
		}

		else
		{
			//Set host connection parameters.
			var socketio = io.connect(window.location.hostname+':3000');
		}
		
		window.onload = init();

		//When recieves a message from server.
		socketio.on('messageToClient', function(data)
		{
			var chatLog = document.getElementById('chatBox');

			dataUri = "<img src='" + data['image'] + "' />";

			chatLog.innerHTML = chatLog.innerHTML + '<div><pre>' + dataUri + ' <b>' + data['username'] + ':</b><br>' +urlify(data['message']) + '</pre></div>';
			sound();
			window.scrollTo(0, document.body.scrollHeight);
		});

		function init()
		{
			var messageForm = document.getElementById('messageForm');

			messageForm.onsubmit = send;
		}

		//Send the message to server.
		function send()
		{
			var message = document.getElementById('Message');
			
			if(message.value != '')
			{
				socketio.emit('messageToServer',
				{
					message: message.value,
					username: "<?php echo Yii::app()->user->name; ?>", 
					image: "<?php echo YiiIdenticon::getImageDataUri(Yii::app()->user->getId(), 20); ?>"
				});

				message.value = '';
			}
			return false;
		}

		function urlify(text) 
		{
			var urlRegex = /(https?:\/\/[^\s]+)/g;
			
			return text.replace(urlRegex, function(url)
			{
				return '<a href="' + url + '" target="_blank">' + url + '</a>';
			});
		}
	</script>
</body>
</html>