# refactool

"refactool" is short for "refactoring tool". It's a toolset for code refactoring.

## Installation

*refactool require php5.3 or higher*

refactool is [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) compliant and can be installed using [Composer](http://getcomposer.org/).  Add `sandysong/refactool` to your `composer.json`
    {
        "require": {
            "sandysong/refactool": "*"
        }
    }
    
If you're new to Composer...

 - [Download and build Composer](http://getcomposer.org/download/)
 - Make it [globally accessible](http://getcomposer.org/doc/00-intro.md#globally)
 - `cd` to your the directory where you'll be writing your Commando script and run `composer install`

*Currently installing via Composer is the only supported option.*

## Using refactool
### Rename
You can use *bin/rname* to do some rename stuff such as:
+ rename filename to fit [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) standard
+ rename classname or method name by preg_replace

You can type *rname --help* to see help information

    songqi@ubuntu:~/work/$ rname --help
    /home/songqi/work/refactool/bin/rname
    
    Usage: /home/songqi/work/refactool/bin/rname [OPTIONS] src dest
    
    Change directory structure or class name to fit the standard.
    This tool scan src dir for class definations and put them to a new dir, other files are left.
    It does not support namespace yet
    
    src
         srcRequired.  src directory of your code
    dest
         destRequired.  dest directory to generate code
    
    --help
         Show the help page for this command.
    
    -i/--input <argument>
         Regex to match input files, default is '/\.php$/'
    
    -p/--pattern <argument>
         pattern to match your class name or method name
    
    -r/--replace <argument>
         replacement to replace your class or method name
    
    -s/--standard <argument>
         naming standard, avalible standards are: psr0, yaf_controller
    
    -t/--target <argument>
         if you want to rename class or method, specify target here: class, method
         
#### examples
**Restructure the directory/file name to fit [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) standard**
    
    $rname /path/to/your/code/source /path/to/put/things/in

**Rename all class that match 'Comm_(.*)' to 'Common_$1'**
    
    $rname -t class -p '/Comm_(.*)/' -r 'Common_$1' /path/to/your/code/source /path/to/put/things/in

It will change all class/interface name in:
* **class/interface defination:** class Comm_Foo {}
* **extends:** class Foo extends Comm_Foo {}
* **implements:** class Foo implements Comm_Foo {}
* **instantiation:** $obj = new Comm_Foo();
* **static call:** $res = Comm_Foo::hello();

**Rename all method that match 'run' to 'indexAction'**

    $rname -t method -p 'run' -r 'indexAction' /path/to/your/code/source /path/to/put/things/in

It will change all method name in:
* **method defination:** public function run() {}
* **method call:** $obj->run();
* **static method call:** Comm_Foo::run();

**Rename class/interface and make it match yaf_controller's naming standard**

    $rname -t class -p '/Controller_(.*)/' -r '$1' -s yaf_controller /path/to/your/code/source /path/to/put/things/in

You should find out that [php-yaf](https://github.com/laruence/php-yaf)'s controller has another naming standard that not compliant with  [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) in this ways:

**Each part of controller name has first character uppercase and the rest lowercase**

So this is a right format:

    Aoo_Boo_CooController
    
And this is not:

    AOO_BOO_COOController
    
**Controller's classname must ended with "Controller" but the filename are not:**

Controller name:

    Aoo_Boo_CooController
    
File name:

    Aoo/Boo/Coo.php
    
So yaf_controller standard will do this:
+ when output code to file, rtrim 'Controller' from the classname and then generate the filename and path.
+ when match and replace class name with regex, strtolower and ucfirst each part of classname and then add 'Controller' to the end of classname.
