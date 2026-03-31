<?php

declare(strict_types=1);

namespace ArchTech\SEO\Commands;

use Illuminate\Console\Command;
use Intervention\Image\ImageManager;

class GenerateFaviconsCommand extends Command
{
    protected $signature = 'seo:generate-favicons {from?}';

    protected $description = 'Generate favicons based on a source file';

    public function handle(): int
    {
        $path = $this->argument('from') ?? public_path('assets/logo.png');

        if (! is_string($path)) {
            $this->error('The `from` argument must be a string.');

            return self::FAILURE;
        }

        $this->info('Generating favicons...');

        if (! class_exists(ImageManager::class)) {
            $this->error('Intervention not available, please run `composer require intervention/image`');

            return self::FAILURE;
        }

        if (! file_exists($path)) {
            $this->error("Given icon path `{$path}` does not exist.");

            return self::FAILURE;
        }

        // Check Intervention Image version
        $interventionV3 = interface_exists('\Intervention\Image\Interfaces\DriverInterface');

        if ($interventionV3) {
            // v3.x implementation
            $manager = new ImageManager(
                new \Intervention\Image\Drivers\Imagick\Driver()
            );

            $this->comment('Generating ico...');
            $image = $manager->read($path);
            $image->resize(32, 32);
            $image->save(public_path('favicon.ico'));

            $this->comment('Generating png...');
            $image = $manager->read($path);
            $image->resize(32, 32);
            $image->save(public_path('favicon.png'));
        } else {
            // v2.x implementation
            $manager = new ImageManager(['driver' => 'imagick']); // @phpstan-ignore argument.type

            $this->comment('Generating ico...');
            $manager
                ->make($path) // @phpstan-ignore method.notFound
                ->resize(32, 32)
                ->save(public_path('favicon.ico'));

            $this->comment('Generating png...');
            $manager
                ->make($path) // @phpstan-ignore method.notFound
                ->resize(32, 32)
                ->save(public_path('favicon.png'));
        }

        $this->info('All favicons have been generated!');

        return self::SUCCESS;
    }
}
