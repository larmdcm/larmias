<?php

declare(strict_types=1);

namespace Larmias\Repository\Models;

use Larmias\Repository\Contracts\ModelInterface;
use think\Model;

class ThinkModel extends Model implements ModelInterface
{
    /**
     * 默认主键
     *
     * @var string
     */
    protected $pk = 'id';

    /**
     * 主动写入时间戳
     *
     * @var boolean
     */
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 创建时间戳字段
     *
     * @var string
     */
    protected $createTime = 'create_time';

    /**
     * 修改时间戳字段
     *
     * @var string
     */
    protected $updateTime = 'update_time';

    /**
     * 软删除字段
     *
     * @var string
     */
    protected string $deleteTime = 'is_delete';

    /**
     * 软删除默认值
     *
     * @var integer
     */
    protected $defaultSoftDelete = 0;
}