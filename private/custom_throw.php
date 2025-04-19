<?php
class CustomThrow {
    public static function exception(string $message): void {
        throw new \Exception($message);
    }
    public static function exceptionWithCondition(mixed $condition, string $message): void {
        if ($condition) throw new \Exception($message);
    }
}