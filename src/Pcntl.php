<?php

namespace drlenux\pcntl;

/**
 * Class Pcntl
 * @package drlenux\pcntl
 */
final class Pcntl
{
    /**
     * @var array
     */
    private static $runners = [];

    /**
     * @param string $name
     * @param int $maxChild
     * @param float $sleep
     * @return bool
     */
    public static function begin(string $name, int $maxChild, float $sleep = 0.1): bool
    {
        self::wait($name, $maxChild, $sleep);
        $pid = pcntl_fork();
        return $pid === 0;
    }

    /**
     * @param string $name
     */
    public static function end(string $name): void
    {
        self::waitNotWork($name);
    }

    /**
     * @param string $name
     * @param int $maxChild
     * @param float $sleep
     */
    private static function wait(string $name, int $maxChild, float $sleep): void
    {
        if (empty(self::$runners[$name])) {
            self::$runners[$name] = [];
        }
        while (count(self::$runners[$name]) >= $maxChild) {
            sleep($sleep);
            foreach (self::$runners[$name] as $pid => $flag) {
                $res = pcntl_waitpid($pid, $status, WNOHANG);
                if ($res == -1 || $res > 0) {
                    unset(self::$runners[$name][$pid]);
                }
            }
        }
    }

    /**
     * @param string $name
     */
    private static function waitNotWork(string $name): void
    {
        if (empty(self::$runners[$name])) {
            self::$runners[$name] = [];
        }
        while (count(self::$runners[$name]) > 0) {
            sleep(0.2);
            foreach (self::$runners[$name] as $pid => $flag) {
                $res = pcntl_waitpid($pid, $status, WNOHANG);
                if ($res == -1 || $res > 0) {
                    unset(self::$runners[$name][$pid]);
                }
            }
        }
    }

    /**
     * @param array $data
     * @param int $countOnPage
     * @return \Generator
     */
    public static function pagination(array $data, int $countOnPage = 1): \Generator
    {
        $a = 0;
        $max = count($data);

        while ($a < $max) {
            yield array_slice($data, $a, $countOnPage);
            $a += $countOnPage;
        }
    }
}