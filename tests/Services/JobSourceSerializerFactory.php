<?php

declare(strict_types=1);

namespace SmartAssert\WorkerClient\Tests\Services;

use SmartAssert\WorkerJobSource\Factory\YamlFileFactory;
use SmartAssert\WorkerJobSource\JobSourceSerializer;
use SmartAssert\YamlFile\Collection\Serializer as YamlFileCollectionSerializer;
use SmartAssert\YamlFile\FileHashes\Serializer as FileHashesSerializer;
use Symfony\Component\Yaml\Dumper as YamlDumper;

class JobSourceSerializerFactory
{
    public function create(): JobSourceSerializer
    {
        $yamlDumper = new YamlDumper();
        $fileHashesSerializer = new FileHashesSerializer($yamlDumper);
        $yamlFileCollectionSerializer = new YamlFileCollectionSerializer($fileHashesSerializer);
        $yamlFileFactory = new YamlFileFactory($yamlDumper);

        return new JobSourceSerializer($yamlFileCollectionSerializer, $yamlFileFactory);
    }
}
