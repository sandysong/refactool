#!/usr/bin/env php
<?php
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

/* define cli options */
$command = new \Commando\Command();
$command->setHelp("Usage: {$argv[0]} [OPTIONS] src dest

Change directory structure or class name to fit the standard. 
This tool scan src dir for class definations and put them to a new dir, other files are left.
It does not support namespace yet");
$command->argument()
	->require()
	->expectsFile()
	->title('src')
	->describedAs('src directory of your code');
$command->argument()
	->require()
	->title('dest')
	->describedAs('dest directory to generate code');
$command->option('s')
	->aka('standard')
	->describedAs('naming standard, avalible standards are: psr0, yaf_controller')
	->must(function($standard){
		$standards= array('psr0','yaf_controller');
		return in_array($standard, $standards);
	});
$command->option('i')
	->aka('input')
	->describedAs("Regex to match input files, default is '/\.php$/'");
$command->option('t')
	->aka('target')
	->describedAs('if you want to rename class or method, specify target here: class, method')
	->must(function($target) {
		$targets = array('class','method');
		return in_array($target, $targets);
	});
$command->option('p')
	->aka('pattern')
	->describedAs('pattern to match your class name or method name');
$command->option('r')
	->aka('replace')
	->describedAs('replacement to replace your class or method name');
if ($command['t']){
	$command->option('p')->require();
	$command->option('r')->require();
}
$command->parse();
/* do process */
if (!file_exists($command[1])){
	mkdir($command[1], 0755, true);
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($command[0]));
$regex = $command['input']?:'/\.php$/';
$files = new RegexIterator($files, $regex);

$parser = new PHPParser_Parser(new PHPParser_Lexer());
$traverser = new PHPParser_NodeTraverser();

$visitor = new Refactool_Rename();
$visitor->setOption($command);
$traverser->addVisitor($visitor);

foreach($files as $file) {
	$code = file_get_contents($file);
	$stmts = $parser->parse($code);
	try {
		$traverser->traverse($stmts);
	} catch (Exception $e) {
		echo "{$file}\t".$e->getMessage()."\n";		
	}
}
