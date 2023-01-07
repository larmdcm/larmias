<?php

declare(strict_types=1);

namespace Larmias\Repository\Models\Think\Concerns;

use think\model\concern\SoftDelete;
use think\Model;

/**
 * 数据软删除
 * @mixin Model
 */
trait BooleanSoftDelete
{
    use SoftDelete;

    /**
     * 删除当前的记录
     * @access public
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->isExists() || $this->isEmpty() || false === $this->trigger('BeforeDelete')) {
            return false;
        }


        $name = $this->getDeleteTimeField();
        $force = $this->isForce();

        if ($name && !$force) {
            // 软删除
            $this->set($name, 1);

            $result = $this->exists()->withEvent(false)->save();

            $this->withEvent(true);
        } else {
            // 读取更新条件
            $where = $this->getWhere();

            // 删除当前模型数据
            $result = $this->db()
                ->where($where)
                ->removeOption('soft_delete')
                ->delete();

            $this->lazySave(false);
        }

        // 关联删除
        if (!empty($this->relationWrite)) {
            $this->autoRelationDelete($force);
        }

        $this->trigger('AfterDelete');

        $this->exists(false);

        return true;
    }
}