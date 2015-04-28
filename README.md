## 基于字典检索、RMM算法的分词处理

- 分词

```php
// 如果有自动加载机制，这个可以省略
require('Lib/Consts.php');
require('Lib/Compile.php');
require('Buffer/Char.php');
require('Buffer/Filec.php');
require('Lib/Retrieval.php');
require('Lib/Splitword.php');

$splitword = \Lib\Splitword::getInstance();
$splitword->initRetrieval('Bin/compile.ctx');

$splitword->exec('极致纤薄一体时尚机身 三星A3仅售1770');
print_r($splitword->getRetrieved());

Array
(
    [66d954] => 仅售
    [779c4e] => 三星A3
    [634334] => 机身
    [0ccaec] => 时尚
    [a32dc5] => 一体
    [0965c8] => 纤薄
    [7b8f34] => 极致
)
```

- 检索

```php
$retrieval = \Lib\Retrieval::getInstance();
$retrieval->setFile('Bin/compile.ctx')->init();

var_dump($retrieval->match('测试'));
```