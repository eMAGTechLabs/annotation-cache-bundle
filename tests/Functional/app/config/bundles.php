<?php

declare(strict_types=1);

return [
    EmagTechLabs\AnnotationCacheBundle\AnnotationCacheBundle::class => ['all' => true],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],//['test_cachable_methods' => true],
];
