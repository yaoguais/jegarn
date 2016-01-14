## how to use phpunit

### 1. make sure you configuration

```

	1. see app/bootstrap.php
	change 'define('TEST_HOST', 'http://192.168.199.243');' to your web server config
	2. see web config
	config file: web-chat-system/config/application.ini
	there is three part:
	one) database config
	two) cache config
	three) chat config
	3. start a server application matched your chat config
	example:
	$ cd examples/web-chat-system
	$ php server.php your_host your_port
	4. start unit test by './phpunit' and check the result
	notice: before your test, clear data of cache database carefully

```