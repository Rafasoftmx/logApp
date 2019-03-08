<?PHP

/*
  _                                                            
 | |               /\                                          
 | | ___   __ _   /  \   _ __  _ __                            
 | |/ _ \ / _` | / /\ \ | '_ \| '_ \                           
 | | (_) | (_| |/ ____ \| |_) | |_) |                          
 |_|\___/ \__, /_/    \_\ .__/| .__/                           
  _____    __/ |_       | |   | | __ _     ___   ___  __  ___  
 |  __ \  |___/ _|      |_|   |_|/ _| |   |__ \ / _ \/_ |/ _ \ 
 | |__) |__ _| |_ __ _ ___  ___ | |_| |_     ) | | | || | (_) |
 |  _  // _` |  _/ _` / __|/ _ \|  _| __|   / /| | | || |\__, |
 | | \ \ (_| | || (_| \__ \ (_) | | | |_   / /_| |_| || |  / / 
 |_|  \_\__,_|_| \__,_|___/\___/|_|  \__| |____|\___/ |_| /_/  
                                                               
                                                                 
                                                                  
                                                             
                                                               
                                                                            

* Simple class to handle logging files
* create and enable/disble predefined loging files
* use predefined files or create custom ones
* manage oversized files automatically rotates them and delete old files

 * see examples at the end of this file

*/

class logApp
{
	public static  $logDir = "logs/";// directory path where le logs going to be storage
	public static  $logMaxSize = ((1024*1024)*10); //max file size of log. Size in bytes (1Mb = 1024 * 1024);
	
	public static  $logPrint = false;// if true, prints all messages send to the logs
	
	// if true, saves a messages in .log from debug, Info, Notice... Emergency methods
	public static  $debug = true;
	public static  $info = true;
	public static  $notice = true;
	public static  $warning = true;
	public static  $error = true;
	public static  $critical = true;
	public static  $alert = true;
	public static  $emergency = true;	
	
	
	
	public static  $permissions = 0755; // dir or file access permissions used when creates
	//0755 -Everything for owner, read and execute for everybody else
	//0600 -Read and write for owner, nothing for everybody else
	//0644 -Read and write for owner, read for everybody else
	//0755 -Everything for owner, read and execute for others
	//0750 -Everything for owner, read and execute for owner's group
	

	/**
	 * rotate log files, if the file exceeds "$logMaxSize", the files is renames with prefix "old-" and a new files is created.
	 * if a "old-" file exist is deleted. so only 2 files of the same type of log going to exist.
	 * 
	 * @access private static
	 * @return void
	 * 
	 */
   private static function rotateLogs()
   {	
		if(is_dir(self::$logDir))// id dir and exist
		{			
			$ficheros  = scandir(self::$logDir);
			foreach ($ficheros as $key => $value) 
			{
				if (!in_array($value,array(".",".."))) 
				{
					if(strrpos($value,'old-') === false) // not contain 'old-' prefix
					{
					$file = self::$logDir . DIRECTORY_SEPARATOR . $value;
					$file2 = self::$logDir . DIRECTORY_SEPARATOR . 'old-' .$value;
					
					 if(is_file($file))
					 {
						 if(filesize($file) > self::$logMaxSize )// if exceeds the max file size
						 {
							 if(file_exists($file2))// if exist a previous 'old-' file, deletes them
							 {
								 unlink($file2);
							 }
							 rename($file,$file2);// and create a new 'old-' file
						 }
					 }
					}
				}
			}
		} 

   }//EOF
	

	/**
	 * get trace info as parameter from a "debug_backtrace() PHP function" and return a formatted string
	 * 
	 * 
	 * @access private static
	 * @return string
	 * @param array $debug_backtrace
	 */
   private static function trace($debug_backtrace = NULL)
   {   
	   
	   if($debug_backtrace == NULL || is_array($debug_backtrace) == false)
	   {
		   return "";
	   }
	   
	   
	   $infotrace = "";

			$infotrace = "Trace:".PHP_EOL;
			
			foreach($debug_backtrace as $key => $trace)	
			{				
				
				$infotrace .= $key." - ";
				if(array_key_exists("file",$trace))
				{
					$infotrace .= $trace["file"];
				}
				if(array_key_exists("line",$trace))
				{
					$infotrace .= " at line " . $trace["line"];
				}
				if(array_key_exists("class",$trace))
				{
					$infotrace .= " Class: '" . $trace["class"]."'";
				}
				
				$type = "";
				if(array_key_exists("type",$trace))
				{
					if($trace["type"] == "->")
					{
						$type = " Method: ";
					}
					elseif($trace["type"] == "::")
					{
						$type = " Static Method: ";
					}
					else
					{
						$type = " Function: ";
					}
				}

				if(array_key_exists("function",$trace))
				{
					$infotrace .= $type . "'" .$trace["function"]."'";
				}
				if(array_key_exists("args",$trace))
				{
					$infotrace .= PHP_EOL ."Arguments: ".  str_replace ("\n",PHP_EOL,print_r($trace["args"],true));
				}
				if(array_key_exists("object",$trace))
				{
					$infotrace .= PHP_EOL . "Object:" . PHP_EOL . str_replace ("\n",PHP_EOL,print_r($trace["object"],true));
				}
			
			}
	   
	   
	return $infotrace;

   }//EOF

	/**
	 * formats a string message and backtrace.
	 * Also prints messages if enables printing and ramdomly checks the size of files to rotate the files.
	 * 
	 * @access private static
	 * @return string
	 * @param string $message
	 * @param array $debug_backtrace
	 *
	 */
   private static function formatMessage($title,$message,$debug_backtrace = NULL) {	   

   	   //check directory, else we create them
		if(!is_dir(self::$logDir))
		{
			mkdir(self::$logDir, self::$permissions , true);
		}
		
	   	// randomly verify files size for rotation
	  	if(rand(0,100) == 0)// probability of 1/100
		{
			logApp::rotateLogs();
		}
	   
	   	$infotrace = logApp::trace($debug_backtrace); //get formatted string from debug_backtrace array
	    $msg = "";
	   	$title = "█ " . date("d-m-Y H:i:s") . ' - ' . $title . " █";
	   
	    $msg .= str_repeat("█",strlen($title)-4 ) . PHP_EOL;
	   	$msg .= $title . PHP_EOL;
	    $msg .= str_repeat("█",strlen($title) -4 ) . PHP_EOL;
	    $msg .= $message . PHP_EOL;
	    $msg .= logApp::trace($debug_backtrace);
	   	$msg .= PHP_EOL. PHP_EOL;

	   	
		if(self::$logPrint) // if print is enabled
		{
			echo "<hr/>".$title."<br/>";
			print "<pre>";
			print $message . PHP_EOL;
			print_r($infotrace);
			print "</pre>";
		}
	   
	   return $msg;
   }//EOF



	/**
	 * writes a formatted message including debug_backtrace if any to a files using the function error_log() of PHP
	 * 
	 * 
	 * @access private static
	 * @return string
	 * @param string $message
	 * @param string $file
	 * @param array debug_backtrace
	 */
   private static function writeMessage($title,$message,$file = "",$debug_backtrace = NULL) 
   {
    	error_log(logApp::formatMessage($title,$message,$debug_backtrace) , 3, self::$logDir .$file);
   }//EOF
	
	
	/**
	 * receives all the static functions called for this class,
	 * and evaluate them to make a log file in base of the name of the function called 
	 * 
	 * @access private static
	 * @return string
	 * @param string $name
	 * @param array $arguments
	 * 
	 */
    public static function __callStatic($name, $arguments)
    {
		$enabled = null;
		if( property_exists(static::class, $name ) )//concatenate to check if exist property in class "name"
		{
			$enabled = self::${$name}; // get the value
		}

		
		if( $enabled !== null )
		{			
			if($enabled == true)
			{
				$message = "";
				$debug_backtrace = null;
				
				if(array_key_exists (0,$arguments))
				{
					$message = $arguments[0];
				}
				
				if(array_key_exists (1,$arguments))
				{
					$debug_backtrace = $arguments[1];
				}

				if($message != "")
				{
					logApp::writeMessage($message,$name.".log",$debug_backtrace); 	
				}
				
			}
			
		}
		else
		{
			$title = "";
			$message = "";
			$debug_backtrace = null;
			
			if(array_key_exists (0,$arguments))
			{
				$title = $arguments[0];
			}
			if(array_key_exists (1,$arguments))
			{
				$message = $arguments[1];
			}

			if(array_key_exists (2,$arguments))
			{
				$debug_backtrace = $arguments[2];
			}

			if($message != "")
			{
				logApp::writeMessage($title,$message,$name.".log",$debug_backtrace); 	
			}
				
		}
		
    }
	

	
}//EOC

/*
										Examples
----------------------------------------------------------------------------------------

The static class can create a file log just callin logApp::NameOfLogFile("title","message",debug_backtrace());

the parameters we need to use are:

1. title: short string title to describe the logging.
2. message: string message of the reason of the logging.
3. debug_backtrace: the result of the debug_backtrace() function. (optional) http://php.net/manual/en/function.debug-backtrace.php


we can create many logs as we want, all of them is going to be saved in logApp::logDir.


example:

logApp::debug("title","message",debug_backtrace());



we can use the predefined logs:

- debug: logApp::debug("message");
- info: logApp::info("message");
- notice: logApp::notice("message");
- warning: logApp::warning("message");
- error: logApp::error("message");
- critical: logApp::critical("message");
- alert: logApp::alert("message");
- emergency: logApp::emergency("message");

these can be turned ON or OFF changin the class properties:

	public static  $debug = true;
	public static  $info = true;
	public static  $notice = true;
	public static  $warning = true;
	public static  $error = true;
	public static  $critical = true;
	public static  $alert = true;
	public static  $emergency = true;
	
Also we can add others or adequate the class to our needs, 

for add logs ON/OFF functionality just add new boolean property.
example 

public static  $name = true;


and we call:

logApp::name("message"); // it will save in file

but if  $name = false;

logApp::name("message"); // it will NOT save in file



roration log files:

wen a log file reaches the logApp::logMaxSize the class renames it to 'old-logName.log'.
if the file 'old-' exist is deleted automatically. so only we going to have maximum 2 files



*/
?>