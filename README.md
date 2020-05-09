# A simple (private) Homegirl file server
This little php script serves the "data"-directory for Homegirl similar to http://homegirl.zone/

## Installation
1. Just place .htaccess, index.php and the two folders somewhere on a Apache webserver.
2. Open the startup.lua file of Homegirl in an editor and add following line (in case your server runs on localhost:8888): `fs.mount("privateworld", "http://localhost:8888/")`
3. (optional) Open the index.php file in an editor, change some configuration values and replace the content in the data/readme.txt file with a nice welcome text

## Links
[Homegirl](https://github.com/poeticAndroid/homegirl)