<?php
/**
 * This file is part of the Ghostscript test package
 *
 * @author Daniel Schröder <daniel.schroeder@gravitymedia.de>
 */

namespace GravityMedia\GhostscriptTest\Device;

use GravityMedia\Ghostscript\Device\AbstractDevice;
use GravityMedia\Ghostscript\Ghostscript;
use GravityMedia\Ghostscript\Input;
use GravityMedia\Ghostscript\Process\Argument;
use GravityMedia\Ghostscript\Process\Arguments;

/**
 * The abstract device test class.
 *
 * @package GravityMedia\GhostscriptTest\Devices
 *
 * @covers  \GravityMedia\Ghostscript\Device\AbstractDevice
 *
 * @uses    \GravityMedia\Ghostscript\Ghostscript
 * @uses    \GravityMedia\Ghostscript\Input
 * @uses    \GravityMedia\Ghostscript\Device\CommandLineParameters\EpsTrait
 * @uses    \GravityMedia\Ghostscript\Device\CommandLineParameters\FontTrait
 * @uses    \GravityMedia\Ghostscript\Device\CommandLineParameters\IccColorTrait
 * @uses    \GravityMedia\Ghostscript\Device\CommandLineParameters\InteractionTrait
 * @uses    \GravityMedia\Ghostscript\Device\CommandLineParameters\OtherTrait
 * @uses    \GravityMedia\Ghostscript\Device\CommandLineParameters\OutputSelectionTrait
 * @uses    \GravityMedia\Ghostscript\Device\CommandLineParameters\PageTrait
 * @uses    \GravityMedia\Ghostscript\Device\CommandLineParameters\RenderingTrait
 * @uses    \GravityMedia\Ghostscript\Device\CommandLineParameters\ResourceTrait
 * @uses    \GravityMedia\Ghostscript\Process\Argument
 * @uses    \GravityMedia\Ghostscript\Process\Arguments
 */
class AbstractDeviceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns an OS independent representation of the commandline.
     *
     * @param string $commandline
     *
     * @return mixed
     */
    protected function quoteCommandLine($commandline)
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            return str_replace('"', '\'', $commandline);

        }

        return $commandline;
    }

    /**
     * @param array $args
     *
     * @return AbstractDevice
     */
    protected function createDevice(array $args = [])
    {
        $ghostscript = new Ghostscript();
        $arguments = new Arguments();
        $arguments->setArguments($args);

        return $this->getMockForAbstractClass(AbstractDevice::class, [$ghostscript, $arguments]);
    }

    public function testArgumentGetter()
    {
        $device = $this->createDevice(['-dFoo=/Bar']);
        $method = new \ReflectionMethod(AbstractDevice::class, 'getArgument');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($device, '-dBaz'));

        /** @var \GravityMedia\Ghostscript\Process\Argument $argument */
        $argument = $method->invoke($device, '-dFoo');
        $this->assertInstanceOf(Argument::class, $argument);
        $this->assertSame('/Bar', $argument->getValue());
    }

    public function testArgumentTester()
    {
        $device = $this->createDevice(['-dFoo=/Bar']);
        $method = new \ReflectionMethod(AbstractDevice::class, 'hasArgument');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($device, '-dBar'));
        $this->assertTrue($method->invoke($device, '-dFoo'));
    }

    public function testStringParameterTester()
    {
        $device = $this->createDevice(['-sFoo=/Bar']);

        $this->assertFalse($device->hasStringParameter('Bar'));
        $this->assertTrue($device->hasStringParameter('Foo'));
    }

    public function testTokenParameterTester()
    {
        $device = $this->createDevice(['-dFoo=/Bar']);

        $this->assertFalse($device->hasTokenParameter('Bar'));
        $this->assertTrue($device->hasTokenParameter('Foo'));
    }

    public function testArgumentValueGetter()
    {
        $device = $this->createDevice(['-dFoo=/Bar']);
        $method = new \ReflectionMethod(AbstractDevice::class, 'getArgumentValue');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($device, '-dBaz'));
        $this->assertSame('/Bar', $method->invoke($device, '-dFoo'));
    }

    public function testStringParameterValueGetter()
    {
        $device = $this->createDevice(['-sFoo=/Bar']);

        $this->assertNull($device->getStringParameterValue('Bar'));
        $this->assertSame('/Bar', $device->getStringParameterValue('Foo'));
    }

    public function testTokenParameterValueGetter()
    {
        $device = $this->createDevice(['-dFoo=/Bar']);

        $this->assertNull($device->getTokenParameterValue('Bar'));
        $this->assertSame('/Bar', $device->getTokenParameterValue('Foo'));
    }

    public function testArgumentSetter()
    {
        $ghostscript = new Ghostscript();
        $arguments = new Arguments();

        /** @var AbstractDevice $device */
        $device = $this->getMockForAbstractClass(AbstractDevice::class, [$ghostscript, $arguments]);

        $method = new \ReflectionMethod(AbstractDevice::class, 'setArgument');
        $method->setAccessible(true);
        $method->invoke($device, '-dFoo=/Bar');

        $argument = $arguments->getArgument('-dFoo');
        $this->assertInstanceOf(Argument::class, $argument);
        $this->assertSame('/Bar', $argument->getValue());
    }

    public function testStringParameterSetter()
    {
        $ghostscript = new Ghostscript();
        $arguments = new Arguments();

        /** @var AbstractDevice $device */
        $device = $this->getMockForAbstractClass(AbstractDevice::class, [$ghostscript, $arguments]);

        $device->setStringParameter('Foo', 'Bar');

        $argument = $arguments->getArgument('-sFoo');
        $this->assertInstanceOf(Argument::class, $argument);
        $this->assertSame('Bar', $argument->getValue());
    }

    public function testTokenParameterSetter()
    {
        $ghostscript = new Ghostscript();
        $arguments = new Arguments();

        /** @var AbstractDevice $device */
        $device = $this->getMockForAbstractClass(AbstractDevice::class, [$ghostscript, $arguments]);

        $device->setTokenParameter('Foo', 42);

        $argument = $arguments->getArgument('-dFoo');
        $this->assertInstanceOf(Argument::class, $argument);
        $this->assertEquals(42, $argument->getValue());
    }

    public function testBooleanParameterSetter()
    {
        $ghostscript = new Ghostscript();
        $arguments = new Arguments();

        /** @var AbstractDevice $device */
        $device = $this->getMockForAbstractClass(AbstractDevice::class, [$ghostscript, $arguments]);

        $device->setBooleanParameter('Foo', 'someTrueishValue');
        $device->setBooleanParameter('Bar', 0);

        $argument = $arguments->getArgument('-dFoo');
        $this->assertInstanceOf(Argument::class, $argument);
        $this->assertSame('true', $argument->getValue());

        $argument = $arguments->getArgument('-dBar');
        $this->assertInstanceOf(Argument::class, $argument);
        $this->assertSame('false', $argument->getValue());
    }

    public function testProcessCreation()
    {
        $process = $this->createDevice()->createProcess();

        $this->assertEquals("'gs'", $this->quoteCommandLine($process->getCommandLine()));
    }

    public function testProcessCreationWithInput()
    {
        $file = __DIR__ . '/../../data/input.pdf';
        $processInput = fopen($file, 'r');
        $code = '.setpdfwrite';

        $input = new Input();
        $input->addFile($file);
        $input->setProcessInput($processInput);
        $input->setPostScriptCode($code);

        $process = $this->createDevice()->createProcess($input);

        $this->assertEquals("'gs' '-c' '$code' '-f' '$file' '-'", $this->quoteCommandLine($process->getCommandLine()));
        $this->assertEquals($processInput, $process->getInput());

        fclose($processInput);
    }

    public function testProcessCreationWithPostScriptInput()
    {
        $input = '.setpdfwrite';

        $process = $this->createDevice()->createProcess($input);

        $this->assertEquals("'gs' '-c' '$input'", $this->quoteCommandLine($process->getCommandLine()));
    }

    public function testProcessCreationWithFileInput()
    {
        $input = __DIR__ . '/../../data/input.pdf';

        $process = $this->createDevice()->createProcess($input);

        $this->assertEquals("'gs' '-f' '$input'", $this->quoteCommandLine($process->getCommandLine()));
    }

    public function testProcessCreationWithResourceInput()
    {
        $input = fopen(__DIR__ . '/../../data/input.pdf', 'r');

        $process = $this->createDevice()->createProcess($input);

        $this->assertEquals("'gs' '-'", $this->quoteCommandLine($process->getCommandLine()));
        $this->assertEquals($input, $process->getInput());

        fclose($input);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testProcessCreationThrowsExceptionOnMissingInputFile()
    {
        $input = new Input();
        $input->addFile('/path/to/input/file.pdf');

        $this->createDevice()->createProcess($input);
    }
}
