<?php

declare(strict_types=1);

namespace AdrianMejias\Blunder;

use Throwable;
use Exception;

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
     * Open with.
     *
     * @var null|string
     */
    public ?string $openWith = 'phpstorm';

    /**
     * Editors.
     *
     * @var array
     */
    public array $editors = [
        'sublime' => 'subl://open?url=file://%file&line=%line',
        'textmate' => 'txmt://open?url=file://%file&line=%line',
        'emacs' => 'emacs://open?url=file://%file&line=%line',
        'macvim' => 'mvim://open/?url=file://%file&line=%line',
        'phpstorm' => 'phpstorm://open?file=%file&line=%line',
        'idea' => 'idea://open?file=%file&line=%line',
        'vscode' => 'vscode://file/%file:%line',
        'atom' => 'atom://core/open/file?filename=%file&line=%line',
        'espresso' => 'x-espresso://open?filepath=%file&lines=%line',
        'netbeans' => 'netbeans://open/?f=%file:%line',
    ];

    /**
     * Error levels.
     *
     * @var int
     */
    public int $levels = E_ALL | E_STRICT;

    /**
     * Variables.
     *
     * @var array
     */
    public array $variables = [];

    public function __destruct()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * Register blunder instance handler.
     *
     * @return \AdrianMejias\Blunder
     */
    public function register(): Blunder
    {
        $environment = getenv('APP_ENV') ?
            getenv('APP_ENV') : 'local';

        if ($environment !== 'local') {
            throw new Exception(
                'This library should only be ran locally.',
                E_USER_WARNING
            );
        }

        $this->mode = defined('HHVM_VERSION') ? 'HHVM' : 'PHP';
        $this->openWith = getenv('BLUNDER_OPEN_WITH') ?
            getenv('BLUNDER_OPEN_WITH') : '';

        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError'], $this->levels);
        register_shutdown_function([$this, 'handleShutdown']);

        return $this;
    }

    /**
     * Set open with.
     *
     * @param string $openWith
     * @return void
     */
    public function setOpenWith(string $openWith): void
    {
        $this->openWith = $openWith;
    }

    /**
     * Handle shutdown.
     *
     * @return bool
     */
    public function handleShutdown(): bool
    {
        $error = error_get_last();

        return $this->handleError(
            (int) ($error['type'] ?? 0),
            $error['message'] ?? '',
            $error['file'] ?? __FILE__,
            (int) ($error['line'] ?? __LINE__)
        );
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
            $e::class,
            $e->getCode(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $backtrace,
            $e->statusCode ?? 0
        );

        return $this->render(true);
    }

    /**
     * Handle error.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     */
    public function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool {
        $type = $this->getErrorType($errno);

        $this->setStackTrace($type, $errno, $errstr, $errfile, $errline, [], 0);

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
            'open_with' => $this->getOpenWith($file, $line),
            'code' => $code,
            'http_code' => $httpCode == 0 ? http_response_code() : $httpCode,
            'backtrace' => $backtrace,
        ];
    }

    /**
     * Get open with.
     *
     * @return null|string
     */
    public function getOpenWith(string $file, int $line): ?string
    {
        return str_replace(
            ['%file', '%line'],
            [rawurlencode($file), $line],
            $this->editors[$this->openWith] ?? ''
        );
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

            if ($i == ($line - 1)) {
                $this->preview .= '<div class="flex justify-start align-middle items-center -my-1">';
                $this->preview .=
                    '<div class="flex align-center items-center justify-center w-16 px-2 py-3 bg-gray-300 border-gray-400 border-t-2">' .
                    ($i + 1) .
                    '</div>';
                $this->preview .=
                    '<div class="bg-red-200 text-red-800 px-2 border-white border-t-2 py-3 w-full">' .
                    static::entities($text) .
                    '</div>';
                $this->preview .= '</div>';
                continue;
            }

            $this->preview .= '<div class="flex justify-start align-middle items-center -my-1">';
            $this->preview .=
                '<div class="flex align-center items-center justify-center w-16 px-2 py-3 bg-gray-300 border-gray-400 border-t-2">' .
                ($i + 1) .
                '</div>';
            $this->preview .= '<div class="bg-white px-2 py-3 border-white border-t-2 w-full">' .
                static::entities($text) .
                '</div>';
            $this->preview .= '</div>';
        }

        return $this->preview;
    }

    /**
     * Convert all applicable characters to HTML entities.
     *
     * @param string $string
     * @return string
     */
    public static function entities(string $string): string
    {
        return htmlentities($string, ENT_QUOTES, 'utf-8', false);
    }

    /**
     * Render output.
     *
     * @return bool
     */
    public function render()
    {
        $this->variables = [
            'Backtrace' => $this->stackTrace['backtrace'],
            'Get Request' => $_GET ?? [],
            'Post Request' => $_POST ?? [],
            'Files Request' => $_FILES ?? [],
            'Cookie' => $_COOKIE ?? [],
            'Session' => $_SESSION ?? [],
            'Server' => $_SERVER ?? [],
            'Environment' => $_ENV ?? [],
        ];

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
