<?php
/**
 * Copyright (c) 2011 Tudor Barbu <miau at motane dot lu>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Parser for the cmd line arguments
 *
 * @category ZFAdminCli
 * @package ZFAdminCli_Getopt
 * @copyright ZFAdmin
 * @licence MIT http://www.opensource.org/licenses/mit-license.php
 * @author Tudor Barbu <miau@motane.lu>
 * @since v.0.1.0
 */

class ZFAdminCli_Getopt_Getopt
{
	/**
	 * Parse the cmd line options
	 * 
	 * @access public
	 * @return array
	 */
	public function getopt()
	{
		$getopt = new Zend_Console_Getopt(
			array(
				'help|h'			=> 'Display the help menu',
				'path|p=s'			=> 'Full path to the /application folder',
				'module|m-s'		=> 'Current module, defaults to none',
			)
		);

		$options = array();

		try {
			$getopt->parse();

			if ($getopt->getOption('help')) {
				throw new ZFAdminCli_Getopt_Exception(
					$getopt->getUsageMessage()
				);
			}

			$options['path'] = $getopt->getOption('path');
			if (null == $options['path']) {
				throw new ZFAdminCli_Getopt_Exception(
					'The "path" parameter is manadatory! Call --help for details!'
				);
			}

			$options['module'] = $getopt->getOption('module') ?: '';
			$options['action'] = 'parse_acls';
		}
		catch(Zend_Console_Getopt_Exception $e) {
			throw new ZFAdminCli_Getopt_Exception($e->getMessage());
		}

		return $options;
	}
}
