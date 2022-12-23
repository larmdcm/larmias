<?php

function yield_func1(): Generator
{
    echo "run yield" . PHP_EOL;
    $result = yield 'test' => 1 . PHP_EOL;
    echo $result . PHP_EOL;
    yield 2 . PHP_EOL;
    return 'a' . PHP_EOL;
}


function yield_func1_test()
{
    $gen = yield_func1();
    echo $gen->key() . ':' . $gen->current();
    //$gen->next();
    $gen->send('123');
    echo $gen->current();
    $gen->next();
    echo $gen->getReturn();
}

/**
 * Class YieldScheduler
 */
Class YieldScheduler
{
    /**
     * @var array $gens
     */
    public $gens = array();

    /**
     * 新增任务到 调度器
     *
     * @param Generator $gen
     * @param null $key
     *
     * @return  $this
     */
    public function add($gen, $key = null)
    {
        if (null === $key) {
            $this->gens[] = $gen;
        } else {
            $this->gens[$key] = $gen;
        }
        return $this;
    }

    /**
     * 开始
     */
    public function start()
    {
        $keepRun = true;
        /**
         * @var Generator   $gen
         */
        $gen = null;
        do {

            // 循环调度任务
            foreach ($this->gens as $id => $gen) {
                $re = $gen->current();
                echo 'generator id: ' . $id . ' run, get current re : ' . $re . PHP_EOL;
                $gen->next();
            }

            // 检查任务是否已完成
            foreach ($this->gens as $id => $gen) {
                $check = $gen->valid();
                if (!$check) {
                    // 已执行完毕的任务就可以踢出任务调度队列了
                    unset($this->gens[$id]);
                }
            }

            // 调度器是否完成所有任务
            if (0 >= count($this->gens)) {
                $keepRun = false;
            }
        } while ($keepRun);
    }
}

function yieldFunc($max = 10)
{
    for($i = 0; $i < $max; $i ++) {
        (yield $i);
    }
    return $i;
}

$gen1 = yieldFunc(3);
$gen2 = yieldFunc(5);

$scheduler = new YieldScheduler();
$scheduler->add($gen1)->add($gen2);
$scheduler->start();