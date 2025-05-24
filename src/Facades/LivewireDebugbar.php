<?php

namespace Vherbaut\LivewireDebugbar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vherbaut\LivewireDebugbar\LivewireDebugbar
 */
class LivewireDebugbar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Vherbaut\LivewireDebugbar\LivewireDebugbar::class;
    }
}
