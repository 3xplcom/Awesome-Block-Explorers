<?php declare(strict_types = 1);

$result = [
    'blockchains' => [],
    'explorers' => [],
];

echo "Read blockchain JSONs...\n";

$blockchains = scandir(__DIR__ . '/../Chains');

for ($i = 2, $c = count($blockchains); $i < $c; $i++)
{
    $t = str_pad($blockchains[$i].'...', 23);
    echo "Reading {$t}\n";

    try
    {
        $file = file_get_contents(__DIR__ . '/../Chains/'.$blockchains[$i]);
        if($file !== false) {
            $blockchain = json_decode($file, associative: true, flags: JSON_THROW_ON_ERROR);
            $result['blockchains'][$blockchain['id']] = $blockchain;
        } else
        {
            throw new Error($blockchains[$i] . 'is not a file');
        }

    } catch (Throwable $t)
    {
        echo "❌ Error: {$t->getMessage()}\n";
        exit(2);
    }
}

echo "Read explorer JSONs...\n";

$explorers = scandir(__DIR__ . '/../Explorers');

for ($i = 2, $c = count($explorers); $i < $c; $i++)
{
    $t = str_pad($explorers[$i].'...', 23);
    echo "Reading {$t}\n";

    try
    {
        $file = file_get_contents(__DIR__ . '/../Explorers/'.$explorers[$i]);
        if($file !== false) {
            $result['explorers'][] = json_decode($file, associative: true, flags: JSON_THROW_ON_ERROR);
        } else
        {
            throw new Error($explorers[$i] . 'is not a file');
        }

    } catch (Throwable $t)
    {
        echo "❌ Error: {$t->getMessage()}\n";
        exit(2);
    }
}

if(!is_dir(__DIR__ . '/../build')) {
    mkdir(__DIR__ . '/../build');
}

echo "\nBuilding JSON file\n";

try
{
    $status = file_put_contents(
        filename:__DIR__ . '/../build/build.json',
        data: json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );

    if($status === false) {
        throw new Error();
    }
}
catch (Throwable $t)
{
    echo "❌ Error building JSON: {$t->getMessage()}\n";
    exit(2);
}


echo "Building YAML file\n";

try
{
    $status = yaml_emit_file(
        filename: __DIR__ . '/../build/build.yml',
        data: $result,
        encoding: YAML_UTF8_ENCODING
    );

    if($status === false) {
        echo "❌ Error building YAML\n";
        exit(2);
    }
}
catch (Throwable $t)
{
    echo "❌ Error building YAML: {$t->getMessage()}\n";
    exit(2);
}

echo "\n✅ Successfully built";
