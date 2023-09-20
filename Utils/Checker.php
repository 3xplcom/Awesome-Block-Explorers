<?php declare(strict_types = 1);

/*  This is a simple script to check whether the explorers are set up correctly and respond with code 200.
 *  Tested on PHP 8.2 with php8.2-curl enabled. Distributed under the MIT software license, see LICENSE.md.  */

$explorers = scandir(__DIR__ . '/../Explorers');
$chains = scandir(__DIR__ . '/../Chains');
$lib = [];
$errors = [];

// First, we check the validity of chain JSON files

echo "Checking chain JSONs...\n";

for ($i = 2, $c = count($chains); $i < $c; $i++)
{
    $t = str_pad($chains[$i] . '...', 23);
    echo "\tChecking {$t}";

    try
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../Chains/' . $chains[$i]), associative: true, flags: JSON_THROW_ON_ERROR);

        if (!isset($json['id']))
            throw new Error('`id` is not set');
        if (!isset($json['name']))
            throw new Error('`name` is not set');
        if (!key_exists('coingecko', $json['foreign_ids']))
            throw new Error('`foreign_ids.coingecko` is not set');
        if (!key_exists('coinmarketcap', $json['foreign_ids']))
            throw new Error('`foreign_ids.coinmarketcap` is not set (can be null)');
        if (!key_exists('binance', $json['foreign_ids']))
            throw new Error('`foreign_ids.binance` is not set (can be null)');
        if (!isset($json['examples']['block']))
            throw new Error('`examples.block` is not set');
        if (!isset($json['examples']['transaction']))
            throw new Error('`examples.transaction` is not set');
        if (!isset($json['examples']['address']))
            throw new Error('`examples.address` is not set');

        $lib[($json['id'])] = $json;

        echo "✅\n";
    }
    catch (Throwable $t)
    {
        echo "❌ Error: {$t->getMessage()}\n";
        $errors[] = $t->getMessage();
    }
}

// Second, we check the validity of explorer json files

echo "Checking explorer JSONs...\n";

for ($i = 2, $c = count($explorers); $i < $c; $i++)
{
    $t = str_pad($explorers[$i] . '...', 23);
    echo "\tChecking {$t}";

    try
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../Explorers/' . $explorers[$i]), associative: true, flags: JSON_THROW_ON_ERROR);

        if (!isset($json['id']))
            throw new Error('`id` is not set');
        if (!isset($json['name']))
            throw new Error('`name` is not set');
        if (!isset($json['description']))
            throw new Error('`description` is not set');
        if (!isset($json['link']))
            throw new Error('`link` is not set');
        if (!key_exists('x', $json))
            throw new Error('`x` is not set (can be null)');
        if (!key_exists('discord', $json))
            throw new Error('`discord` is not set (can be null)');
        if (!isset($json['blockchains']))
            throw new Error('`blockchains` is not set');
        if (!isset($json['search']))
            throw new Error('`search` is not set');

        foreach ($json['blockchains'] as $id => $blockchain)
        {
            if (!isset($lib[$id]))
                throw new Error("`{$id}` is not a known chain");
            if (!isset($blockchain['homepage']))
                throw new Error("`homepage` is not set for {$id}");
            if (!key_exists('block', $blockchain))
                throw new Error("`block` is not set for {$id} (can be null)");
            if (!key_exists('transaction', $blockchain))
                throw new Error("`transaction` is not set for {$id} (can be null)");
            if (!key_exists('address', $blockchain))
                throw new Error("`address` is not set for {$id} (can be null)");
            if (isset($blockchain['block']) && !str_contains($blockchain['block'], '%block%'))
                throw new Error("`block` should contain `%block%` for {$id}");
            if (isset($blockchain['transaction']) && !str_contains($blockchain['transaction'], '%transaction%'))
                throw new Error("`transaction` should contain `%transaction%` for {$id}");
            if (isset($blockchain['address']) && !str_contains($blockchain['address'], '%address%'))
                throw new Error("`address` should contain `%address%` for {$id}");
            if (isset($blockchain['search']) && !str_contains($blockchain['search'], '%search%'))
                throw new Error("`search` should contain `%search%` for {$id}");
        }

        echo "✅\n";
    }
    catch (Throwable $t)
    {
        echo "❌ Error: {$t->getMessage()}\n";
        $errors[] = $t->getMessage();
    }
}

// Last, we check whether the explorers respond with 200

$curl = curl_init();
curl_setopt($curl, CURLOPT_NOBODY, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 5);

function check_page($url)
{
    global $curl;

    try
    {
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_exec($curl);
        $info = curl_getinfo($curl);

        if ($info['http_code'] !== 200)
            echo "⚠️ Code: {$info['http_code']}\n";
        else
            echo "✅ " . number_format($info['total_time'], decimals: 2). " s. \n";
    }
    catch (Throwable $t)
    {
        echo "⚠️ Error: {$t->getMessage()}\n";
    }
}

echo "Checking explorers...\n";

for ($i = 2, $c = count($explorers); $i < $c; $i++)
{
    $explorer = json_decode(file_get_contents(__DIR__ . '/../Explorers/' . $explorers[$i]), true);

    echo "\tChecking {$explorer['name']}...\n";

    foreach ($explorer['blockchains'] as $j => $chain)
    {
        echo "\t\tChecking {$lib[$j]['name']}...\n";

        if (!isset($lib[$j]))
        {
            echo "\t\tThis chain is not present in the library ❌\n";
            continue;
        }

        if (!is_null($chain['homepage']))
        {
            echo "\t\t\tChecking homepage...    ";
            check_page($chain['homepage']);
        }

        if (!is_null($chain['block']))
        {
            echo "\t\t\tChecking block...       ";
            check_page(str_replace('%block%', $lib[$j]['examples']['block'], $chain['block']));
        }

        if (!is_null($chain['transaction']))
        {
            echo "\t\t\tChecking transaction... ";
            check_page(str_replace('%transaction%', $lib[$j]['examples']['transaction'], $chain['transaction']));
        }

        if (!is_null($chain['address']))
        {
            echo "\t\t\tChecking address...     ";
            check_page(str_replace('%address%', $lib[$j]['examples']['address'], $chain['address']));
        }
    }
}

if(count($errors)) {
    exit(2);
}
