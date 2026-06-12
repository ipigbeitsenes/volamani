<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function flashSuccess(string $message): void
    {
        session()->flash('success', $message);
    }

    protected function flashError(string $message): void
    {
        session()->flash('error', $message);
    }

    protected function flashInfo(string $message): void
    {
        session()->flash('info', $message);
    }

    protected function flashWarning(string $message): void
    {
        session()->flash('warning', $message);
    }
}
