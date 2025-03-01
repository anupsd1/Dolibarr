<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    test/unit/CameraAndXRAYTest.php
 * \ingroup miscellaneous
 * \brief   PHPUnit test for CameraAndXRAY class.
 */

namespace test\unit;

/**
 * Class CameraAndXRAYTest
 * @package Testmiscellaneous
 */
class CameraAndXRAYTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Global test setup
	 */
	public static function setUpBeforeClass()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Unit test setup
	 */
	protected function setUp()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Verify pre conditions
	 */
	protected function assertPreConditions()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * A sample test
	 */
	public function testSomething()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
		// TODO: test something
		$this->assertTrue(true);
	}

	/**
	 * Verify post conditions
	 */
	protected function assertPostConditions()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Unit test teardown
	 */
	protected function tearDown()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Global test teardown
	 */
	public static function tearDownAfterClass()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Unsuccessful test
	 *
	 * @param  Exception $e    Exception
	 * @throws Exception
	 */
	protected function onNotSuccessfulTest(Exception $e)
	{
		fwrite(STDOUT, __METHOD__ . "\n");
		throw $e;
	}
}
