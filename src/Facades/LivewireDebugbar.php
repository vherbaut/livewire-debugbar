<?php

namespace BoutonPlace\LivewireDebugbar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BoutonPlace\LivewireDebugbar\LivewireDebugbar
 */
class LivewireDebugbar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BoutonPlace\LivewireDebugbar\LivewireDebugbar::class;
    }
}
