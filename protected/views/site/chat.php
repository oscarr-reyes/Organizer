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
			<div class="navbar-header" id="tabs">
				<a class="navbar-brand" href="#"><?php echo CHtml::encode(Yii::app()->name); ?></a>
				<ul class="nav navbar-nav">
					<li class="active" data-tab="master">
						<a href="#">Master</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="row">
		<div id="userBox">
			<div id="users" style="display:none">
				<div class="container">
					<b>Users</b>
					<ol id="userList"></ol>
				</div>
			</div>
		</div>
		<div id="chatBox">
			<div id="channel-master"></div>
		</div>
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
					'placeholder'=>'Connecting...',
					'autoComplete'=>'false',
					'disabled'=>true,
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
		var user = "<?php echo Yii::app()->user->name; ?>";
		var tabsArray = [];
		$(document).ready(function()
		{
			init();

			$('#tabs').on('click', 'a.tab', function()
			{
				switchChannel($(this));
			});

			//Add animation to left-div.
			$('#userBox').unbind('click').click(function(event)
			{
				if($(this).hasClass('active'))
				{
					$(this).removeClass('active');

					$('#chatBox').animate(
					{
						'padding-left':'3.5%'
					});
					
					$(this).animate(
					{
						width:'1%'
					});
					toggleUsers();
				}

				else if(!$(this).hasClass('active'))
				{
					$(this).addClass('active');

					$('#chatBox').animate(
					{
						'padding-left':'17.5%'
					});
					
					$(this).animate(
					{
						width:'15%'
					});
					toggleUsers();
				}
			});
		});

		if(typeof io === 'undefined')
		{
			$('#Message').attr('placeholder', 'Cannot connect to server...');
			$('#Message').attr('disabled', true);
			$('#users').html('<div class="container">Unable to connect</div>');
		}

		else
		{
			//Set host connection parameters.
			var socketio = io.connect(window.location.hostname+':3000',
				{
					query:'user=' + user + '&icon=' + '<?php echo YiiIdenticon::getImageDataUri(Yii::app()->user->getId(), 20); ?>',
					test: 'test'
				}
			);
			$('#Message').attr('placeholder', 'Message');
			$('#Message').attr('disabled', false);
		}

		//When recieves a message from server.
		socketio.on('messageToClient', function(data)
		{
			var chatLog = document.getElementById('channel-master');

			dataUri = "<img src='" + data['image'] + "' />";

			chatLog.innerHTML = chatLog.innerHTML + '<div><pre>' + dataUri + ' <b>' + data['username'] + ':</b><br>' +urlify(data['message']) + '</pre></div>';
			sound();
			window.scrollTo(0, document.body.scrollHeight);
		});

		//Display all connected clients.
		socketio.on('showClients', function(data)
		{
			var usersToShow = "";
			var arrUsers = data['users'];
			
			for (var usr in arrUsers)
			{
				var icon = data['icons'][arrUsers[usr]];

				icon = icon.replace(/ /g, "+");

				usersToShow +="<li class='user' onClick=addChannel('"+ arrUsers[usr] +"');><img src='"+icon+"'></img> "+arrUsers[usr]+"</li>";
			}

			$("#userList").html(usersToShow);

			joinSound();
		});

		socketio.on('recievePrivateMessage', function(data)
		{	
			if(!$('#channel-' + data['username']).length)
				addChannel(data['username']);

			var chatLog = $('#channel-' + data['username']);

			dataUri = "<img src='" + data['image'] + "' />";

			chatLog.append('<div><pre>' + dataUri + ' <b>' + data['username'] + ':</b><br>' +urlify(data['message']) + '</pre></div>');
			sound();
			window.scrollTo(0, document.body.scrollHeight);
		});
		socketio.on('sendHistory',function(data){
			var historyText = "";
			if(data["history"].length>0){
				for (var i = 0; i < data["history"].length; i++) {
					var uriData = "<img src='" + data["history"][i]['icon'] + "' />";
					historyText += '<div><pre>' + uriData + ' <b>' + data["history"][i]["user"] + ':</b><br>' +urlify(data["history"][i]['msg']) + '</pre></div>';
				}
			}
			$('#channel-master').append(historyText);
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
				if($('a.tab.active').attr('data-tab') == 'master')
				{
					socketio.emit('messageToServer',
					{
						message: message.value,
						username: "<?php echo Yii::app()->user->name; ?>", 
						image: "<?php echo YiiIdenticon::getImageDataUri(Yii::app()->user->getId(), 20); ?>",
					});
				}

				else
				{
					var to = $('a.tab.active').attr('data-tab'),
						chatLog = $('#channel-' + to),
						image = "<img src='<?php echo YiiIdenticon::getImageDataUri(Yii::app()->user->getId(), 20); ?>' />";


					socketio.emit('sendPrivateMessage', to, message.value,
					{
						image: "<?php echo YiiIdenticon::getImageDataUri(Yii::app()->user->getId(), 20); ?>"
					});

					chatLog.append('<div><pre>' + image + ' <b><?php echo Yii::app()->user->name; ?>:</b><br>' +urlify(message.value) + '</pre></div>');
				}

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

		function toggleUsers()
		{
			var tab = $('#users');

			if(tab.hasClass('active'))
			{
				tab.removeClass('active');
				tab.hide(100);
			}

			else if(!tab.hasClass('active'))
			{
				tab.addClass('active');
				tab.show(100);
			}
		}

		function addChannel(channel)
		{
			var chatBox = $('#chatBox');
			var navbar = $('#tabs');

			if(channel != '<?php echo Yii::app()->user->name; ?>' && tabsArray.indexOf(channel)<0)
			{
				var newChannel = '<div id="channel-' + channel + '" style="display:none"></div>';
				var newTab = '<p class="navbar-text menu"><a href="#" class="navbar-link tab" data-tab="'+ channel +'">' + channel + '</a></p>';
				tabsArray.push(channel);
				chatBox.append(newChannel);
				navbar.append(newTab);
			}
		}

		function switchChannel(tag)
		{
			var currentChannel = $('.tab.active');
			var newChannel = tag;

			//hide old channel.
			currentChannel.removeClass('active');
			$('#channel-'+ currentChannel.attr('data-tab')).hide();


			//show new channel.
			newChannel.addClass('active');
			$('#channel-'+ newChannel.attr('data-tab')).show();
		}
		function debugR(pass,callBack){
			socketio.emit('debugR',
			{
				password: pass
			});
		}
	</script>
</body>
</html>