abbyysdk
========

PHP client for [ABBYY Cloud OCR SDK](http://ocrsdk.com/)

Based on [abbyysdk/ocrsdk.com](https://github.com/abbyysdk/ocrsdk.com/tree/master/PHP) PHP example. Added support for HTTP proxy and [disabling the 100-continue header](http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/).

To use you need an account with [ABBYY Cloud OCR SDK](http://ocrsdk.com/). Currently they offer a free trial.

Examples images are in the "images" folder. Set the filename in abbyy_php_example then

php abbyy_php_example.php

will call ABBYY OCR SDK and output file in chosen format.
