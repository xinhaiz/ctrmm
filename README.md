## 基于字典检索、RMM算法的分词处理

- 版本： v0.10 Beta

- 编译
    + `Data/base.ct` 必须存在，但可以为空
    + 新增加的词可以以 `.ct`结尾的文件并放在 `Data/addones` 下而无须理会名字，编译时将自动合并。
    + 新增词可以是中文、数字、英文、中英文数字组合(其它语言系未测试，理论上是可行的)，一行一个方式增加
    + 允许手动设定 `Data/addones` 在位置或重新定义路径
    + 编译后的目标文件默认位置 `Bin/compile.ctx`, 手动设置时不带后缀 `.ctx`

    ```sh
        php -f $PATH/Compile.php
    ```

- 分词

    ```php
        $splitword = \Lib\Splitword::getInstance();
        $splitword->initRetrieval('Bin/compile.ctx');
        $splitword->exec('金蝉脱壳、 百里挑一、 金玉满堂、 背水一战、 霸王别姬、');
        print_r($splitword->getRetrieved());

        Array
        (
            [664c5f] => 霸王别姬
            [c9aeb4] => 背水一战
            [21d5d9] => 金玉满堂
            [6ca208] => 百里挑一
            [2fddc8] => 金蝉脱壳
        )
    ```

    ```php
        $splitword = \Lib\Splitword::getInstance();
        $splitword->initRetrieval('Bin/compile.ctx');

        $splitword->setType('max'); // 最大可能的检索出所有存在于字典的词, 默认： general (小写)

        $splitword->exec('金蝉脱壳、 百里挑一、 金玉满堂、 背水一战、 霸王别姬、');

        print_r($splitword->getRetrieved());

        Array
        (
            [664c5f] => 霸王别姬
            [c9aeb4] => 背水一战
            [21d5d9] => 金玉满堂
            [6ca208] => 百里挑一
            [2fddc8] => 金蝉脱壳
            [b138c8] => 霸王
            [06f557] => 金玉
            [00c20f] => 百里
            [a7645d] => 一战
            [097d0f] => 满堂
            [aa82f9] => 挑一
            [caab23] => 脱壳
            [0c789c] => 金蝉
        )
    ```

- 检索

    ```php
        $retrieval = \Lib\Retrieval::getInstance();
        $retrieval->setFile('Bin/compile.ctx')->init();

        var_dump($retrieval->match('测试'));

        $retrieval->end();
    ```

- 编码

    ```php
        $compile = new \Lib\Compile(); 
        $compile->encode('测试');
        $code = $compile->getCode();
    ```