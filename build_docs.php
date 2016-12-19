#!/usr/bin/env php
<?php
/*
Copyright 2016 Daniil Gentili
(https://daniil.it)
This file is part of MadelineProto.
MadelineProto is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
MadelineProto is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU Affero General Public License for more details.
You should have received a copy of the GNU General Public License along with MadelineProto.
If not, see <http://www.gnu.org/licenses/>.
*/

require_once 'vendor/autoload.php';

$mode = 3;
\danog\MadelineProto\Logger::constructor($mode);

$TL = new \danog\MadelineProto\TL\TL([
    //'mtproto'  => __DIR__.'/src/danog/MadelineProto/TL_mtproto_v1.json', // mtproto TL scheme
    'telegram' => __DIR__.'/src/danog/MadelineProto/TL_telegram_v57.json', // telegram TL scheme
]);

\danog\MadelineProto\Logger::log('Copying readme...');

copy('README.md', 'docs/index.md');

chdir(__DIR__.'/docs/API_docs');

\danog\MadelineProto\Logger::log('Generating documentation index...');

file_put_contents('index.md', '# MadelineProto API documentation (layer 57) 

[Methods](methods/)

[Constructors](constructors/)

[Types](types/)

');

foreach (glob('methods/*') as $unlink) {
    unlink($unlink);
}

if (file_exists('methods')) {
    rmdir('methods');
}

mkdir('methods');

$methods = [];

$types = [];
\danog\MadelineProto\Logger::log('Generating methods documentation...');

foreach ($TL->methods->method as $key => $method) {
    $method = str_replace('.', '_', $method);

    $type = str_replace(['.', '<', '>'], ['_', '_of_', ''], $TL->methods->type[$key]);
    $real_type = preg_replace('/.*_of_/', '', $type);

    $params = '';
    foreach ($TL->methods->params[$key] as $param) {
        if ($param['name'] == 'flags') {
            continue;
        }
        $stype = 'type';
        $link_type = 'types';
        if (isset($param['subtype'])) {
            $stype = 'subtype';
            if ($param['type'] == 'vector') {
                $link_type = 'constructors';
            }
        }
        $ptype = str_replace('.', '_', $param[$stype]);
        switch ($ptype) {
            case 'true':
            case 'false':
                $ptype = 'Bool';
        }
        $params .= "'".$param['name']."' => ";
        $ptype =
            '['.
            str_replace('_', '\_', $ptype).
            '](../'.$link_type.'/'.$ptype.'.md)';

        $params .= (isset($param['subtype']) ? '\['.$ptype.'\]' : $ptype).', ';
    }
    $md_method = '['.str_replace(['_', '.'], ['->', '\_'], $method).']('.$method.'.md)';

    $methods[$method] = '$MadelineProto->'.$md_method.'(\['.$params.'\]) == [$'.str_replace('_', '\_', $type).'](../types/'.$real_type.'.md)  

';

    $params = '';
    $table = empty($TL->methods->params[$key]) ? '' : '### Parameters:

| Name     |    Type       | Required |
|----------|:-------------:|---------:|
';
    foreach ($TL->methods->params[$key] as $param) {
        if ($param['name'] == 'flags') {
            continue;
        }
        $ptype = str_replace('.', '_', $param[isset($param['subtype']) ? 'subtype' : 'type']);
        switch ($ptype) {
            case 'true':
            case 'false':
                $ptype = 'Bool';
        }
        $table .= '|'.str_replace('_', '\_', $param['name']).'|'.(isset($param['subtype']) ? 'Array of ' : '').'['.str_replace('_', '\_', $ptype).'](../types/'.$ptype.'.md) | '.($param['flag'] ? 'Optional' : 'Required').'|
';

        $params .= "'".$param['name']."' => ";
        $params .= (isset($param['subtype']) ? '['.$ptype.']' : $ptype).', ';
    }
    $header = str_replace('_', '\_', '## Method: '.$method.'  

');
    $table .= '

';
    $return = '### Return type: ['.str_replace('_', '\_', $type).'](../types/'.$real_type.'.md)

';
    $example = str_replace('[]', '', '### Example:


```
$MadelineProto = new \danog\MadelineProto\API();
if (isset($token)) {
    $this->bot_login($token);
}
if (isset($number)) {
    $sentCode = $MadelineProto->phone_login($number);
    echo \'Enter the code you received: \';
    $code = \'\';
    for ($x = 0; $x < $sentCode[\'type\'][\'length\']; $x++) {
        $code .= fgetc(STDIN);
    }
    $MadelineProto->complete_phone_login($code);
}

$'.$type.' = $MadelineProto->'.str_replace('.', '->', $method).'(['.$params.']);
```');
    file_put_contents('methods/'.$method.'.md', $header.$table.$return.$example);
}

\danog\MadelineProto\Logger::log('Generating methods index...');

ksort($methods);
file_put_contents('methods/index.md', '# Methods  

'.implode('', $methods));

foreach (glob('constructors/*') as $unlink) {
    unlink($unlink);
}

if (file_exists('constructors')) {
    rmdir('constructors');
}

mkdir('constructors');

$constructors = [];

\danog\MadelineProto\Logger::log('Generating constructors documentation...');

foreach ($TL->constructors->predicate as $key => $constructor) {
    $constructor = str_replace('.', '_', $constructor);

    $type = str_replace(['.', '<', '>'], ['_', '_of_', ''], $TL->constructors->type[$key]);
    $real_type = preg_replace('/.*_of_/', '', $type);

    $params = '';
    foreach ($TL->constructors->params[$key] as $param) {
        if ($param['name'] == 'flags') {
            continue;
        }
        $stype = 'type';
        $link_type = 'types';
        if (isset($param['subtype'])) {
            $stype = 'subtype';
            if ($param['type'] == 'vector') {
                $link_type = 'constructors';
            }
        }

        $ptype = str_replace('.', '_', $param[$stype]);
        switch ($ptype) {
            case 'true':
            case 'false':
                $ptype = 'Bool';
        }

        $params .= "'".$param['name']."' => ";
        $ptype =
            '['.
            str_replace('_', '\_', $ptype).
            '](../'.$link_type.'/'.$ptype.'.md)';

        $params .= (isset($param['subtype']) ? '\['.$ptype.'\]' : $ptype).', ';
    }

    $constructors[$constructor] = '[$'.str_replace('_', '\_', $real_type).'](../types/'.$real_type.'.md) = \['.$params.'\];  

';

    if (!isset($types[$real_type])) {
        $types[$real_type] = [];
    }
    if (!in_array($key, $types[$real_type])) {
        $types[$real_type][] = $key;
    }
    $table = empty($TL->constructors->params[$key]) ? '' : '### Attributes:

| Name     |    Type       | Required |
|----------|:-------------:|---------:|
';
    $params = '';
    foreach ($TL->constructors->params[$key] as $param) {
        if ($param['name'] == 'flags') {
            continue;
        }
        $ptype = str_replace('.', '_', $param[isset($param['subtype']) ? 'subtype' : 'type']);

        $link_type = 'types';
        if (isset($param['subtype'])) {
            if ($param['type'] == 'vector') {
                $link_type = 'constructors';
            }
        }
        switch ($ptype) {
            case 'true':
            case 'false':
                $ptype = 'Bool';
        }
        $table .= '|'.str_replace('_', '\_', $param['name']).'|'.(isset($param['subtype']) ? 'Array of ' : '').'['.str_replace('_', '\_', $ptype).'](../'.$link_type.'/'.$ptype.'.md) | '.($param['flag'] ? 'Optional' : 'Required').'|
';

        $params .= "'".$param['name']."' => ";
        $params .= (isset($param['subtype']) ? '['.$param['type'].']' : $param['type']).', ';
    }
    $params = "['_' => ".$constructor."', ".$params.']';

    $header = str_replace('_', '\_', '## Constructor: '.$constructor.'  

');
    $table .= '


';
    $type = '### Type: ['.str_replace('_', '\_', $real_type).'](../types/'.$real_type.'.md)


';
    $example = '### Example:

```
$'.$constructor.' = '.$params.';
```';
    file_put_contents('constructors/'.$constructor.'.md', $header.$table.$type.$example);
}

\danog\MadelineProto\Logger::log('Generating constructors index...');

ksort($constructors);
file_put_contents('constructors/index.md', '# Constructors  

'.implode('', $constructors));

foreach (glob('types/*') as $unlink) {
    unlink($unlink);
}

if (file_exists('types')) {
    rmdir('types');
}
mkdir('types');

ksort($types);
$index = '';

\danog\MadelineProto\Logger::log('Generating types documentation...');

foreach ($types as $type => $keys) {
    $type = str_replace('.', '_', $type);

    $index .= '['.$type.']('.$type.'.md)  

';
    $constructors = '';
    foreach ($keys as $key) {
        $predicate = str_replace('.', '_', $TL->constructors->predicate[$key]);
        $constructors .= '['.str_replace('_', '\_', $predicate).'](../constructors/'.$predicate.'.md)  

';
    }
    $header = str_replace('_', '\_', '## Type: '.$type.'  

### Possible values (constructors):

');
    file_put_contents('types/'.$type.'.md', $header.$constructors);
}

\danog\MadelineProto\Logger::log('Generating types index...');

file_put_contents('types/index.md', '# Types  

'.$index);

\danog\MadelineProto\Logger::log('Generating additional types...');

file_put_contents('types/string.md', '## Type: string  

A string of variable length.');

file_put_contents('types/bytes.md', '## Type: bytes  

A string of variable length.');

file_put_contents('types/int.md', '## Type: int  

A 32 bit signed integer ranging from -2147483647 to 2147483647.');

file_put_contents('types/long.md', '## Type: long  

A 64 bit signed integer ranging from -9223372036854775807 to 9223372036854775807.');

file_put_contents('types/double.md', '## Type: double  

A double precision number, single precision can also be used (float).');

file_put_contents('types/!X.md', '## Type: !X  

Represents a TL serialized payload.');

file_put_contents('types/X.md', '## Type: X  

Represents a TL serialized payload.');

file_put_contents('constructors/boolFalse.md', '# boolFalse  

Represents boolean with value equal to `false`.');

file_put_contents('constructors/boolTrue.md', '# boolTrue  

Represents boolean with value equal to `true`.');

\danog\MadelineProto\Logger::log('Done!');