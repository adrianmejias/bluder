<?php

declare(strict_types=1);

namespace AdrianMejias\Blunder;

use Throwable;

class Blunder
{
    /**
     * Stacktrace.
     *
     * @var array
     */
    public array $stackTrace;

    /**
     * Preview.
     *
     * @var string
     */
    public string $preview = '';

    /**
     * Mode.
     *
     * @var string
     */
    public string $mode = 'PHP';

    /**
     * Register blunder instance handler.
     *
     * @return \AdrianMejias\Blunder
     */
    public function register(): Blunder
    {
        $this->mode = defined('HHVM_VERSION') ? 'HHVM' : 'PHP';

        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);

        return $this;
    }

    /**
     * Handle exception.
     *
     * @param \Throwable $e
     * @return bool
     */
    public function handleException(Throwable $e): bool
    {
        $backtrace = preg_split('/#[\d]+/', $e->getTraceAsString());

        unset($backtrace[0]);
        array_pop($backtrace);

        $this->setStackTrace(
            'Exception',
            $e->getCode(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $backtrace,
            $e->statusCode ?? 0
        );

        return $this->render();
    }

    /**
     * Handle error.
     *
     * @param integer $code
     * @param string $message
     * @param string $file
     * @param integer $line
     * @return bool
     */
    public function handleError(
        int $code,
        string $message,
        string $file,
        int $line
    ): bool {
        $type = $this->getErrorType($code);

        $this->setStackTrace($type, $code, $message, $file, $line, [], 0);

        return $this->render();
    }

    /**
     * Get error type.
     *
     * @param integer $code
     * @return string
     */
    public function getErrorType(int $code): string
    {
        return [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Depcrecated',
        ][$code] ?? 'Error';
    }

    /**
     * Set stack trace.
     *
     * @param string $type
     * @param integer $code
     * @param string $message
     * @param string $file
     * @param integer $line
     * @param array $backtrace
     * @param integer $httpCode
     * @return array
     */
    public function setStackTrace(
        string $type,
        int $code,
        string $message,
        string $file,
        int $line,
        array $backtrace = [],
        int $httpCode = 0
    ): array {
        return $this->stackTrace = [
            'type' => $type,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'code' => $code,
            'http_code' => $httpCode == 0 ? http_response_code() : $httpCode,
            'backtrace' => $backtrace,
        ];
    }

    /**
     * Set Preview.
     *
     * @return null|string
     */
    public function setPreview(): ?string
    {
        $file = file($this->stackTrace['file']);
        $line = $this->stackTrace['line'];

        $start = ($line - 5 >= 0) ? $line - 5 : $line - 1;
        $end = ($line - 5 >= 0) ? $line + 4 : $line + 8;

        for ($i = $start; $i < $end; $i++) {
            if (!isset($file[$i])) {
                continue;
            }

            $text = trim($file[$i]);

            if ($i == $line - 1) {
                $this->preview .= '<span>' . ($i + 1) . '</span>';
                $this->preview .= '<span>' . $text . '</span><br>';
                continue;
            }

            $this->preview .= '<span>' . ($i + 1) . '</span>';
            $this->preview .= '<span>' . $text . '</span><br>';
        }

        return $this->preview;
    }

    /**
     * Render output.
     *
     * @return bool
     */
    public function render(): bool
    {
        $this->setPreview();

        if (
            !defined('PHPUNIT_COMPOSER_INSTALL') &&
            !defined('__PHPUNIT_PHAR__')
        ) {
            include_once __DIR__ . '/resources/views/app.php';
        }

        return true;
    }
}
