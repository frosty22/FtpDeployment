<?php

namespace FtpDeployment\DI;

use FtpDeployment\DirNotFoundException;
use FtpDeployment\InvalidArgumentException;
use Nette\Config\CompilerExtension;
use Nette\Utils\Validators;

/**
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class FtpDeploymentExtension extends CompilerExtension
{


	/**
	 * @var array
	 */
	private $defaults = array(
		"localPath" => ".",
		"ftp" => array(
			"user" => "",
			"password" => "",
			"host" => "",
			"path" => ""
		),
		"purge" => array(
			"%tempDir%/cache"
		),
		"tempDir" => "%tempDir%/ftp-deployment",
		"filters" => array(),
		"allowDelete" => TRUE,
		"before" => array(),
		"after" => array(),
		"ignore" => array(
				".git*",
				"project.pp[jx]",
				"/deployment.*",
				"/log",
				"temp/*",
				"!temp/proxies",
				"!temp/.htaccess",
				".DS_Store"
		),
		"test" => FALSE
	);


	/**
	 * Base configuration
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);


		// Local path
		Validators::isUnicode($config["localPath"]);

		if (!is_dir($config["localPath"]))
			throw new InvalidArgumentException("Dir '{$config["localPath"]}' not found.");


		// FTP
		if (is_string($config["ftp"])) {
			$ftp = $config["ftp"];
		}
		elseif (is_array($config["ftp"])) {
			Validators::assertField($config["ftp"], "user");
			Validators::assertField($config["ftp"], "password");
			Validators::assertField($config["ftp"], "host");

			$ftp = "ftp://{$config["ftp"]["user"]}:{$config["ftp"]["password"]}@{$config["ftp"]["host"]}/{$config["ftp"]["path"]}";
		}
		else
			throw new InvalidArgumentException("Parameter 'ftp' must be string or array.");

		Validators::isList($config["before"]);
		foreach ($config["before"] as $url)
			Validators::isUrl($url);

		Validators::isList($config["after"]);
		foreach ($config["after"] as $url)
			Validators::isUrl($url);

		Validators::is($config["allowDelete"], "bool");
		Validators::isList($config["ignore"]);
		Validators::isList($config["purge"]);
		Validators::is($config["test"], "bool");

		Validators::is($config["tempDir"], "string");
		if (!is_dir($config["tempDir"]))
			throw new DirNotFoundException("Directory '{$config["tempDir"]}' doesnt exists.");

		// Add definition of command
		$definition = $builder->addDefinition($this->prefix('FtpDeploymentCommand'))
			->setClass('FtpDeployment\Command\FtpDeploymentCommand')
			->addTag('kdyby.console.command')
			->setAutowired(FALSE)
			->addSetup("setLocal", $config["localPath"])
			->addSetup("setRemote", $ftp)
			->addSetup("setPurge", array($config["purge"]))
			->addSetup("setAllowDelete", $config["allowDelete"])
			->addSetup("setIgnore", array($config["ignore"]))
			->addSetup("setBefore", array($config["before"]))
			->addSetup("setAfter", array($config["after"]))
			->addSetup("setTestMode", $config["test"])
			->addSetup("setTempDir", $config["tempDir"]);

		Validators::is($config["filters"], "array");
		foreach ($config["filters"] as $extension => $callback) {
			Validators::isCallable($callback);
			$definition->addSetup("addFilter", array($extension, $callback));
		}

	}


}