# Xantase
A frontend language specially made for beginners
With Xantase you can easly generate webpages like one would normally do with languages like React, Ractive, Vue etcetra, but then more simple

Here is table of contents to easly navigate between capitals:
1. [Syntax](#syntax)
    1. [Functions](#function)
    2. [Variables creation](#createvar)
    3. [Update variables](#setvar)
    4. [Call](#call)
    5. [Spawn](#spawn)
    6. [Foreach](#foreach)
2. [Examples](#examples)
3. [Usages](#usages)

## Syntax
### Function
You can make a function in these ways:

1. ``` function [functionname] with rootdoc data params ```
2. ``` function [functionname] ```
3. ``` function [functionname] with [...] ```

functionname can be anything you want, but it should not include any spaces
the "rootdoc data params" cannot be chanced for the build function for other functions, only with is nessesery when you use parameters

### Createvar

You can create a variable in these ways:

1. ``` create [subtype] [type] called [name] ```
2. ``` create [subtype] [type] called [name] and [setvar] ```

where

types:
1. variable
    1. subtype: string
    2. subtype: number
2. node
    1. all HTML tags
Name can be anything you want, but it should not include any spaces
setvar = see setvar type but you can leave the ``` of [varname] ``` part

### Setvar
You can set a variable in these ways:
1. ``` set [property] [subproperty] of [varname] to [value] ```
2. ``` set value of [varname] to [value] ```
3. ``` set [property] [subproperty] of [varname] from [call] ```

### Call
You can call a function in these ways:
1. ``` call [function] of [varname] with [...] ```
2. ``` call [function] of [varname] ```
3. ``` call [function] with [...] ```
4. ``` call [function] ```
### Spawn
You can spawn a class in these ways:
1. ``` spawn [class] on [append_to_node] using [one_variable] ```
### Foreach
You can make a foreach in these ways:
1. ``` foreach [list] as [listitem] for [command] ```
## Examples
See the ``` Example ``` directory

## Usage
To output to a file:
```php 
<?php 
    include_once "../xantase.php";
    (new Xantase())->xantase_build_output_to_file(__DIR__,__DIR__ . DIRECTORY_SEPARATOR . "js.js");
?>
```