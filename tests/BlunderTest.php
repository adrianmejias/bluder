<?php

declare(strict_types=1);

namespace AdrianMejias\Tests;

use PHPUnit\Framework\TestCase;
use AdrianMejias\Blunder\Blunder;
use Mockery;
use Exception;

class BlunderTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     * @covers \AdrianMejias\Blunder\Blunder
     */
    public function it_can_get_string_of_error_type()
    {
        $mock = Mockery::mock(Blunder::class);
        $mock->allows()->getErrorType(E_WARNING)->andReturn('Warning');
        $mock->allows()->getErrorType(007)->andReturn('Error');
        $blunder = new Blunder;

        $this->assertSame(
            $blunder->getErrorType(E_WARNING),
            $mock->getErrorType(E_WARNING)
        );

        $this->assertSame(
            $blunder->getErrorType(007),
            $mock->getErrorType(007)
        );
    }

    /**
     * @test
     * @covers \AdrianMejias\Blunder\Blunder
     */
    public function it_can_register_error_and_exception_handlers()
    {
        $mock = Mockery::mock(Blunder::class);
        $mock->allows()->register()->andReturnSelf();
        $blunder = new Blunder;

        $this->assertInstanceOf(Blunder::class, $blunder->register());

        $this->assertInstanceOf(Blunder::class, $mock->register());
    }

    /**
     * @test
     * @covers \AdrianMejias\Blunder\Blunder
     */
    public function it_can_run_handle_exception()
    {
        $mock = Mockery::mock(Blunder::class);
        $mock->allows()->handleException(new Exception)->andReturn(true);
        $blunder = new Blunder;

        $this->expectException(Exception::class);

        $this->assertTrue($blunder->handleException(new Exception));
        
        $this->assertTrue($mock->handleException(new Exception));
    }

    /**
     * @test
     * @covers \AdrianMejias\Blunder\Blunder
     */
    public function it_can_run_handle_error()
    {
        $error = [
            'code' => E_ERROR,
            'message' => 'This is a warning.',
            'file' => __FILE__,
            'line' => __LINE__,
        ];

        $mock = Mockery::mock(Blunder::class);
        $mock->allows()->register()->andReturnSelf();
        $mock->allows()->handleError(
            $error['code'],
            $error['message'],
            $error['file'],
            $error['line']
        )->andReturn(true);
        $blunder = new Blunder;

        $result = $blunder->register()->handleError(
            $error['code'],
            $error['message'],
            $error['file'],
            $error['line']
        );
        $this->assertTrue($result);

        $result = $mock->register()->handleError(
            $error['code'],
            $error['message'],
            $error['file'],
            $error['line']
        );
        $this->assertTrue($result);
    }
}
