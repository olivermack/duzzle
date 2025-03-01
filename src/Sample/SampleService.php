<?php

declare(strict_types=1);

namespace Duzzle\Sample;

class SampleService implements SampleServiceInterface
{
    public function __construct(private SampleServiceInterface $decorated)
    {
    }

    public function doStuff(array $options = []): object
    {
        $enhancedOptions = $this->enhanceDefaultOptions($options);
        $res = $this->decorated->doStuff($enhancedOptions);

        return $res;
    }

    private function enhanceDefaultOptions(array $options = []): array
    {
        if (!array_key_exists('test', $options)) {
            $options['test'] = \stdClass::class;
        }

        return $options;
    }
}
