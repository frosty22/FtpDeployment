<?php

namespace FtpDeployment\Command;

use FtpDeployment\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class FtpDeploymentCommand extends Command {


	const DEPLOYMENT_FILE = "ftp-deployment";


	/**
	 * @var string
	 */
	private $local = "";


	/**
	 * @var string
	 */
	private $remote = "";


	/**
	 * @var array
	 */
	private $purge = array();


	/**
	 * @var array
	 */
	private $filters = array();


	/**
	 * @var bool
	 */
	private $allowDelete = TRUE;


	/**
	 * @var array
	 */
	private $before = array();


	/**
	 * @var array
	 */
	private $after = array();


	/**
	 * @var array
	 */
	private $ignore = array();


	/**
	 * @var bool
	 */
	private $testMode = FALSE;


	/**
	 * @var string
	 */
	private $tempDir = "";


	/**
	 * @param string $local
	 */
	public function setLocal($local)
	{
		$this->local = $local;
	}


	/**
	 * @param string $remote
	 */
	public function setRemote($remote)
	{
		$this->remote = $remote;
	}


	/**
	 * @param array $after
	 */
	public function setAfter(array $after)
	{
		$this->after = $after;
	}


	/**
	 * @param boolean $allowDelete
	 */
	public function setAllowDelete($allowDelete)
	{
		$this->allowDelete = $allowDelete;
	}


	/**
	 * @param array $before
	 */
	public function setBefore(array $before)
	{
		$this->before = $before;
	}


	/**
	 * @param array $ignore
	 */
	public function setIgnore(array $ignore)
	{
		$this->ignore = $ignore;
	}


	/**
	 * @param string $extension
	 * @param callable $callable
	 */
	public function addFilter($extension, $callable)
	{
		if (!isset($this->filters[$extension]))
			$this->filters[$extension] = array();

		$this->filters[$extension][] = $callable;
	}


	/**
	 * @param array $purge
	 */
	public function setPurge(array $purge)
	{
		$this->purge = $purge;
	}


	/**
	 * @param boolean $testMode
	 */
	public function setTestMode($testMode)
	{
		$this->testMode = $testMode;
	}


	/**
	 * @param string $tempDir
	 */
	public function setTempDir($tempDir)
	{
		$this->tempDir = $tempDir;
	}


	/**
	 * Configure command
	 */
	protected function configure()
	{
		$this->setName('ftp:deployment')
			->setDescription('Execute FTP deployment');
	}


	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln("FTP deployment started at " . date("Y/m/d H:i"));

		require_once __DIR__ . "/../../../../../../vendor/dg/ftp-deployment/Deployment/libs/Deployment.php";
		require_once __DIR__ . "/../../../../../../vendor/dg/ftp-deployment/Deployment/libs/Logger.php";
		require_once __DIR__ . "/../../../../../../vendor/dg/ftp-deployment/Deployment/libs/Ftp.php";

		$deployment = new \Deployment($this->remote, $this->local, new Logger($output));

		foreach ($this->filters as $ext => $callback) {
			$deployment->addFilter($ext, $callback);
		}

		$deployment->allowDelete = $this->allowDelete;
		$deployment->runAfter = $this->after;
		$deployment->runBefore = $this->before;
		$deployment->ignoreMasks = $this->ignore;
		$deployment->toPurge = $this->purge;
		$deployment->testMode = $this->testMode;
		$deployment->tempDir = $this->tempDir;
		$deployment->deploymentFile = $this->tempDir . "/" . self::DEPLOYMENT_FILE;

		$deployment->deploy();

		return 0;
	}



}