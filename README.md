# logApp

Simple static class to handle logging files

* create and enable/disble predefined loging files.
* use predefined files or create custom ones.
* manage oversized files automatically rotates them and delete old files.

## properties
- **logDir**: directory path where le logs going to be storage, defautl "logs/"
- **logMaxSize**: max file size of log. Size in bytes. default 10MB
- **logPrint**: boolean, if true, prints all messages send to the logs.

#### activates/deactivates  some logs files

- **logDebug**: boolean
- **logInfo**: boolean
- **logNotice**: boolean
- **logWarning**: boolean
- **logError**: boolean
- **logCritical**: boolean
- **logAlert**: boolean
- **logEmergency**: boolean

- **permissions**: dir or file access permissions used when creates. default 0755
```
    0600 -Read and write for owner, nothing for everybody else
    0644 -Read and write for owner, read for everybody else
    0755 -Everything for owner, read and execute for others
    0750 -Everything for owner, read and execute for owner's group
```

The static class can create a file log just callin :
```
    logApp::NameOfLogFile("message",debug_backtrace());
```
the parameters we need to use are:

1. **message**: short string message to describe the reason of the logging.
2. **debug_backtrace**: the result of the debug_backtrace() function. (optional) http://php.net/manual/en/function.debug-backtrace.php

we can create many logs as we want, all of them is going to be saved in `logApp::logDir`

example:
```
    logApp::debug("message",debug_backtrace());
```

result:

```
█████████████████████████████████
█ 06-02-2019 21:10:22 - message █
█████████████████████████████████
Trace:
0 - \logApp\test\test.php at line 27 Class: 'something' Static Method: 'test'
Arguments: Array
(
    [0] => info
)

```


we can use the predefined logs:

- **debug**:
```
 logApp::debug("message");
 ```
- **info**:
```
 logApp::info("message");
```
- **notice**:
```
logApp::notice("message");
```
- **warning**:
```
logApp::warning("message");
```
- **error**:
```
logApp::error("message");
```
- **critical**:
```
logApp::critical("message");
```
- **alert**:
```
logApp::alert("message");
```
- **emergency**:
```
logApp::emergency("message");
```

these can be turned ON or OFF changin the class properties:
```
    public static  $logDebug = true;
    public static  $logInfo = true;
    public static  $logNotice = true;
    public static  $logWarning = true;
    public static  $logError = true;
    public static  $logCritical = true;
    public static  $logAlert = true;
    public static  $logEmergency = true;
```
Also we can add others or adequate the class to our needs.
for add logs ON/OFF functionality just add new boolean property.

example:
```
    public static  $logName = true;
```
for that begin with 'log' and the name of our log, it can be capitalized:
```
    $logName or not: $logname
```
and we call:
```
    logApp::name("message"); // it will save in file
```
but if  $logName = **false**;
```
    logApp::name("message"); // it will NOT save in file
```

### roration log files

wen a log file reaches the `logApp::logMaxSize` the class renames it to 'old-logName.log'.
if the file '**old-**' exist is deleted automatically. so only we going to have maximum 2 files.
