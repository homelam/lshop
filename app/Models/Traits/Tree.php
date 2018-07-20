<?php

namespace App\Models\Traits;

use Encore\Admin\Traits\ModelTree;

trait Tree
{
    use ModelTree;

    /**
     * Get options for Select field in form.
     * @param boolean $behavior 如果是分类的创建或是修改设置为true
     * @return \Illuminate\Support\Collection
     */
    public static function selectOptions($behavior = false)
    {
        $options = (new static())->buildSelectOptions();
        
        return $behavior ? collect($options)->prepend('Root', 0)->all() : collect($options)->all();
    }

    /**
     * Build options of select field in form.
     *
     * @param array  $nodes
     * @param int    $parentId
     * @param string $prefix
     *
     * @return array
     */
    protected function buildSelectOptions(array $nodes = [], $parentId = 0, $prefix = '')
    {
        $prefix = $prefix ?: str_repeat('&nbsp;', 4);

        $options = [];

        if (empty($nodes)) {
            $nodes = $this->allNodes();
        }

        foreach ($nodes as $node) {
            $node[$this->titleColumn] = $prefix . $node[$this->titleColumn];
            if ($node[$this->parentColumn] == $parentId) {
                $children = $this->buildSelectOptions($nodes, $node[$this->getKeyName()], $prefix.$prefix);

                $options[$node[$this->getKeyName()]] = $node[$this->titleColumn];

                if ($children) {
                    $options += $children;
                }
            }
        }

        return $options;
    }
}