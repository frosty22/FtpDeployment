<?php

namespace FtpDeployment;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @copyright Copyright (c) 2013 Ledvinka Vít
 * @author Ledvinka Vít, frosty22 <ledvinka.vit@gmail.com>
 *
 */
class Logger extends \Logger {


	/**
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	private $output;


	/**
	 * @param OutputInterface $output
	 */
	public function __construct(OutputInterface $output)
	{
		$this->output = $output;
	}


	/**
	 * @param string $message
	 */
	public function log($message)
	{
		$this->output->writeln($message);
	}

}