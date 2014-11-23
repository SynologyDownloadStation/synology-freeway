# synology-freeway

This is a basic, but functional, [Free-way.me](http://www.free-way.me) file hosting module for Synology Download Station. It was tested with premium user and Diskstation 5.1. (Download Station 3.5). MIT license: Use this software at your own risk! 

Thanks for these two souces that helped developing this plugin:
* [official developer guide](http://ukdl.synology.com/download/Document/DeveloperGuide/Developer_Guide_to_File_Hosting_Module.pdf)
* [synology-realdebrid](https://github.com/robinwit/synology-realdebrid) (basic but working example for real-debrid.com)

## Bugs / What's missing

* Sensible error handling (if that's even possible with the provided API)
* The `INFO` file does not contain all the hosts RealDebrid can handle: update to suit your needs
* Bug: some hosts (e.g. Oboom) don't provide the correct filename yet. load.php is always returned as filename instead
* Bug: sometimes the download fails and must be repeated (reason: unknown atm) 

## Testing

To test from command line:
```
php test.php
```

## Troubleshooting

If the download fails, try using the website. Usually this means that specific file host is down, or there's something wrong with your login.

## Installing

```
tar zcf free-way.host INFO free-way.php
```
=> And add `free-way.host` as a file hosting module in the Download Station settings.
