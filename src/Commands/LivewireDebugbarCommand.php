<?php

namespace BoutonPlace\LivewireDebugbar\Commands;

use Illuminate\Console\Command;

class LivewireDebugbarCommand extends Command
{
    public $signature = 'LivewireDebugbar';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
